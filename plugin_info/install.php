<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function freeCrystal_install() {
	$Equipement=freeCrystal::AddDevice("Redemarrage","Redemarrage");
	$Commande=freeCrystal::AddCommmande($Equipement,'Serveur','Serveur', "action",'other');
	if (!$Commande->getConfiguration('Code'))
		{
			$Commande->setConfiguration('Code','');
			$Commande->save();
		}
	$Commande=freeCrystal::AddCommmande($Equipement,'Player','Player', "action",'other');
	if (!$Commande->getConfiguration('Code'))
		{
			$Commande->setConfiguration('Code','');
			$Commande->save();
		}
	$cron = cron::byClassAndFunction('freeCrystal', 'getfreeCrystalInformation');
	if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('freeCrystal');
		$cron->setFunction('getfreeCrystalInformation');
		$cron->setEnable(1);
		$cron->setSchedule('* * * * *');
		$cron->save();
	}
}
function freeCrystal_update() {
$cron = cron::byClassAndFunction('freeCrystal', 'getfreeCrystalInformation');
    if (is_object($cron)) {
		$cron->stop();
        $cron->remove();
    }
	$cron = cron::byClassAndFunction('freeCrystal', 'getfreeCrystalInformation');
	if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('freeCrystal');
		$cron->setFunction('getfreeCrystalInformation');
		$cron->setEnable(1);
		$cron->setSchedule('* * * * *');
		$cron->save();
	}
}
function freeCrystal_remove() {
    $cron = cron::byClassAndFunction('freeCrystal', 'getfreeCrystalInformation');
    if (is_object($cron)) {
		$cron->stop();
        $cron->remove();
    }
}

?>
