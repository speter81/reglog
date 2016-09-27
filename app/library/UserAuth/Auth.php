<?php
namespace UserAuth;

use Phalcon\Mvc\User\Component;
use UserAuth\Models\User as User;
use \Swift_Message as Message;

class Auth extends Component {

	public function handleRegistration()
	{
		$name = $this->request->getPost('name');
		$email = $this->request->getPost('email');
		$password = $this->request->getPost('password');
		$passwordVerify = $this->request->getpost('passwordVerify');


		$user = User::findFirst([
			"conditions" => "email = ?1",
			"bind"       => [
				1 => $email,
				]
		]);

		if ($user) {
			$this->flash->error('User already exists with this email address!');
			return ;
		}

		$user = new User;

		if ($user->setUserDetails($name, $email, $password, $passwordVerify)) {
			$user->save();
			$this->sendConfirmationMail($user);
			$this->response->redirect('/');
			$this->view->disable();
			return ;
		} else {
			$this->flash->error('Error saving user data, please try again!');
		}
	}

	public function login($email, $password)
	{
		if ( ! $this->security->checkToken()) {
			$this->userAuth->logFailedAccess();
			$this->flash->error('Invalid request, please try again!');
			return; false;
		}

		if ($this->isCaptchaRequired() && ! $this->captchaValid()) {
			$this->flash->error('Please use the captcha!');
			return; false;
		}

		$user = User::findFirst([
				"conditions" => "email = ?1",
				"bind"       => [
					1 => $email,
					]
			]);

		if ( ! $user) {
			$this->userauth->logAccessFailure();
			$this->flash->error('User not exists!');
			return; false;
		}
		if ( ! $this->security->checkHash($password, $user->password)) {
			$this->flash->error('Invalid password!');
			return; false;
		}

		$this->session->set('name', $user->getName());
		return true;
	}

	public function isCaptchaRequired()
	{
		$di = $this->getDI();
		return false;
	}

	private function captchaValid()
	{
		$provider = Request::getProvider();
		$provider->setBaseUri('https://www.google.com/recaptcha/');
		$response = $provider->post('api/siteverify', [
			'secret' => '6LeHWwcUAAAAAETRaBYYgnwQsD7vxvp3Ioixf9Kc',
			'response' => $this->request->getPost('g-recaptcha-response'),
			'remoteip' => $_SERVER['REMOTE_ADDR']
		]);

		$captchaApiResponse = json_decode($response->body);
		if ( ! $captchaApiResponse->success) {
			return false;
		}
		return true;
	}

	public function logFailedAccess()
	{
		$visitorIp = $_SERVER['REMOTE_ADDR'];
	}

	private function sendConfirmationMail($user)
	{
 		$emailTemplate = $this->view->getRender('emails','welcome',['fullName' => $user->getName()]);

		$mailer = $this->mailer;

		$message = Message::newInstance('Welcome to our site')
			->setFrom(array('saraiptr@gmail.com' => 'Peter'))
			->setTo(array($user->getEmail() => $user->getName()))
			->setBody($emailTemplate, 'text/html');

		return $mailer->send($message);
	}

}
