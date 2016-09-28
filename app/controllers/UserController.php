<?php

use Phalcon\Http\Client\Request;
use Auth\Models\AccessLog;

class UserController extends ControllerBase
{

	public function loginAction()
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$this->view->setVar('reCaptchaSiteKey', '6LeHWwcUAAAAAH3NeUx8P8KDZ6EbAwPFfEndfXUQ');
		$this->view->setVar('captchaNeeded', $this->userAuth->isCaptchaRequired($ip));

		if ($this->session->has('name')) {
			$this->response->redirect('/user/welcome');
			$this->view->disable();
			return ;
		}

		if ($this->request->isPost()) {
			$email = $this->request->getPost('email');
			$password = $this->request->getPost('password');

			if ($this->userAuth->login($email, $password)) {
				$this->response->redirect('/user/welcome');
				$this->view->disable();
				return ;
			}
		}
	}

	public function registrationAction()
	{
		if ($this->request->isPost()) {
			$this->userAuth->handleRegistration();
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

	public function activateAction($verificationHash)
	{
		if ($this->userAuth->verifyActivationHash($verificationHash)) {
			$this->flash->success('Activation successful! Please log in!');
		} else {
			$this->flash->error('Link is expired or invalid hash!');
		}
	}
}

