<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function freeCrystal_install() {
$Commande=freeCrystal::AddCommmande($Reseau,'Réponse au ping','RéponsePing', "info",'string',1);
$Commande->remove();
}

function freeCrystal_update() {

}

function freeCrystal_remove() {
}
?>
							
