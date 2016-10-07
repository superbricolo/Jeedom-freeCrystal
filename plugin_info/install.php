<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function freeCrystal_install() {
	$equipement=eqLogic::byLogicalId('Reseau','freeCrystal');
	$Commande=$equipement->getCmd(null,'RéponsePing');
	$Commande->remove();
}
function freeCrystal_update() {
	$equipement=eqLogic::byLogicalId('Reseau','freeCrystal');
	$Commande=$equipement->getCmd(null,'RéponsePing');
	$Commande->remove();
}

function freeCrystal_remove() {
}
?>
							
