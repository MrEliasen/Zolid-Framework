<?php

class PluginExampleModelsHome extends PluginExampleModel
{
	public function generateNumber($from, $to)
	{
		return rand($from, $to);
	}
}