<?php
namespace UserAuth;

use Phalcon\Mvc\User\Component;
use UserAuth\Models\User as User;
use UserAuth\Models\AccessLog as AccessLog;
use Phalcon\Http\Client\Request;
use \Swift_Message as Message;

class Auth extends Component {

	public function handleRegistration()
	{
		$name = $this->request->getPost('name');
		$email = $this->request->getPost('email');
		$password = $this->request->getPost('password');
		$passwordVerify = $this->request->getpost('passwordVerify');

		$user = User::findFirstByEmail($email);

		if ($user) {
			$this->flash->error('User already exists with this email address!');
			return ;
		}

		$user = new User;

		if (($result = $user->setUserDetails($name, $email, $password, $passwordVerify)) === true) {
			$user->save();
			$this->sendConfirmationMail($user);
			$this->response->redirect('/');
			$this->view->disable();
			return ;
		} else {
			$this->flash->error($result);
		}
	}

	public function login($email, $password)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
/*
		if ( ! $this->security->checkToken()) {
			$this->logFailedAccess($ip);
			$this->flash->error('Invalid request, please try again!');
			return false;
		}
*/
		if ($this->isCaptchaRequired($ip, $email) && ! $this->captchaValid()) {
			$this->logFailedAccess($ip, $email);
			$this->flash->error('Please use the captcha!');
			return false;
		}

		$user = User::findFirstByEmail($email);

		if ( ! $user) {
			$this->logFailedAccess($ip);
			$this->flash->error('User not exists!');
			return false;
		}

		if ($user->status == 0) {
			$this->logFailedAccess($ip);
			$this->flash->error('User is not active, please activate first!');
			return false;
		}

		if ( ! $this->security->checkHash($password, $user->password)) {
			$this->logFailedAccess($ip, $email);
			$this->flash->error('Invalid password!');
			return false;
		}

		$this->session->set('name', $user->getName());
		return true;
	}

	public function isCaptchaRequired($ip, $email = NULL)
	{
		/*
		 * Return true if any of the below cases are valid
		 * after 3 failed logins from the same IP address
		 * after 500 failed logins from the same network with mask bits 24
		 * after 1000 failed logins from the same network with mask bits 16=
		 * after 3 failed login of the same user
		 */

		$failedLogins = AccessLog::getFailedAttemptsFromIP($ip);

		if ($failedLogins >= 3) {
			return true;
		}

		$failedLoginsFromNetworkC = AccessLog::getFailedAttemptsFromNetwork($ip, 24);
		if ($failedLoginsFromNetworkC >= 500) {
			return true;
		}
		$failedLoginsFromNetworkB = AccessLog::getFailedAttemptsFromNetwork($ip, 16);
		if ($failedLoginsFromNetworkB >= 1000) {
			return true;
		}

		if(isset($email)) {
			$user = User::findFirstByEmail($email);
			if ($user) {
				$failedUserLogins = AccessLog::getFailedAttemptsForUser($user->getUserId());
				if ($failedUserLogins >= 3) {
					return true;
				}
			}
		}

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

	public function logFailedAccess($ip, $email = NULL)
	{
		$userId = 0;
		if (isset($email)) {
			$user = User::findFirstByEmail($email);
			if ($user) {
				$userId = $user->getUserId();
			}
		}

		AccessLog::logAccessFailure($ip, $userId);
		AccessLog::logAccessFailureClassB($ip, $userId);
		AccessLog::logAccessFailureClassC($ip, $userId);
		return true;
	}

	private function sendConfirmationMail($user)
	{
		$activationLink = $this->getActionvationLink($user);
 		$emailTemplate = $this->view->getRender('emails','welcome',
				[
					'fullName' => $user->getName(),
					'activationLink' => $activationLink
				]
			);

		$mailer = $this->mailer;

		$message = Message::newInstance('Welcome to our site')
			->setFrom(array('peter@livesystems.hu' => 'Peter'))
			->setTo(array($user->getEmail() => $user->getName()))
			->setBody($emailTemplate, 'text/html');

		return $mailer->send($message);
	}

	private function getActionvationLink($user)
	{
		$ttl = time() + 86400;
		return $this->url->get($this->url->getBaseUri().'user/activate/'.$user->getActivationHash($ttl));
	}

	public function verifyActivationHash($userHash)
	{
		list($hash, $userId, $ttl) = explode(':',$userHash);
		if ($ttl < time()) {
			return false;
		}

		$user = User::findFirstById($userId);
		if ( ! $user) {
			return false;
		}

		if ($user->status != 0) {
			return false;
		}

		$verificationHash = $user->getActivationHash($ttl);
		if ($userHash != $verificationHash) {
			return false;
		}

		$user->status = 1;
		return $user->save();
	}
}
