<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function freeCrystal_install() {
	$equipement=eqLogic::byLogicalId('Reseau','freeCrystal');
	if(is_object($equipement)){
		$Commande=$equipement->getCmd(null,'RéponsePing');
		$Commande->remove();
		$Commande=$equipement->getCmd(null,'AdresseIPprivee');
		$Commande->remove();
	}
							
}
function freeCrystal_update() {
	$equipement=eqLogic::byLogicalId('Reseau','freeCrystal');
	if(is_object($equipement)){
		$Commande=$equipement->getCmd(null,'RéponsePing');
		$Commande->remove();
		$Commande=$equipement->getCmd(null,'AdresseIPprivee');
		$Commande->remove();
	}
}

function freeCrystal_remove() {
}
?>
							
