<?php

class UserController extends ControllerBase
{

	public function loginAction()
	{
		if ($this->session->has('name')) {
			$this->response->redirect('/user/welcome');
			$this->view->disable();
			return ;
		}

		if ($this->request->isPost()) {
			$email = $this->request->getPost('email');
			$password = $this->request->getPost('password');
			$user = User::findFirst(array('email' => $email));
			if ($user->password == $password) {
				$this->session->set('name', $user->getName());
				$this->response->redirect('/user/welcome');
				$this->view->disable();
				return ;
			} else {
				$this->flash->error('Invalid password');
			}
		}

	}

	public function registrationAction()
	{

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

