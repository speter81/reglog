<?php

use Phalcon\Http\Client\Request;

class UserController extends ControllerBase
{

	public function loginAction()
	{
		if ($this->session->has('name')) {
			$this->response->redirect('/user/welcome');
			$this->view->disable();
			return ;
		}
		// Site key: 6LeHWwcUAAAAAH3NeUx8P8KDZ6EbAwPFfEndfXUQ
		// Secret key:
		$visitorIp = $_SERVER['REMOTE_ADDR'];
		$reCaptcha = true;
		$this->view->setVar('needCaptcha', $reCaptcha);

		if ($this->request->isPost()) {

			if ( ! $this->security->checkToken()) {
				$this->flash->error('Invalid request, please try again!');
				return;
			}

			if ($reCaptcha) {
				$provider = Request::getProvider();
				$provider->setBaseUri('https://www.google.com/recaptcha/');
				$response = $provider->post('api/siteverify', [
					'secret' => '6LeHWwcUAAAAAETRaBYYgnwQsD7vxvp3Ioixf9Kc',
					'response' => $this->request->getPost('g-recaptcha-response'),
					'remoteip' => $visitorIp
				]);

				$captchaApiResponse = json_decode($response->body);
				if ( ! $captchaApiResponse->success) {
					$this->flash->error('Please use the captcha!');
					return;
				}
			}

			$email = $this->request->getPost('email');
			$password = $this->request->getPost('password');
			$user = User::findFirst([
					"conditions" => "email = ?1",
					"bind"       => [
						1 => $email,
						]
				]);

			if ( ! $user) {
				$this->flash->error('User not exists!');
				return ;
			}
			if ( ! $this->security->checkHash($password, $user->password)) {
				$this->flash->error('Invalid password!');
				return ;
			}

			$this->session->set('name', $user->getName());
			$this->response->redirect('/user/welcome');
			$this->view->disable();
			return ;
		}

	}

	public function registrationAction()
	{
		if ($this->request->isPost()) {
			$name = $this->request->getPost('name');
			$email = $this->request->getPost('email');
			$password = $this->request->getPost('password');
			$passwordVerify = $this->request->getpost('passwordVerify');

			if ($password != $passwordVerify) {
				$this->flash->error('Passwords do not match!');
				return;
			}

			$user = new User;

			if ( ! $user->setEmail($email)) {
				$this->flash->error('Invalid email address!');
				return;
			}

			if ( ! $user->setPassword($password)) {
				$this->flash->error('Provided password is too weak!');
				return ;
			}

			if ( ! $user->setName($name)) {
				$this->flash->error('Name contains invalid characters');
				return ;
			}

			if ($user->save()) {
				$this->response->redirect('/');
				$this->view->disable();
				return ;
			} else {
				$this->flash->error('Error saving user data, please try again!');
			}

		}
	}

	public function welcomeAction()
	{
		if ( ! $this->session->has('name')) {
			$this->response->redirect('/');
			$this->view->disable();
			return;
		}

		$this->view->setVar('name', $this->session->get('name'));
	}

	public function logoutAction()
	{
		$this->session->destroy();
		$this->response->redirect('/');
		$this->view->disable();
		return;
	}

}

