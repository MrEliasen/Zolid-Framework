<?php

class PluginExampleControllerHome extends PluginExampleController
{
	public function generateRandomNumber()
	{
		//just to demonstrate how to use the models as well, I will do that here.
		$number = $this->model->generateNumber(1, 100);

		return $number; // a number between 1 and 100;
	}
}