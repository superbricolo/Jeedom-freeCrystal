<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class freeCrystal extends eqLogic {
        public static function dependancy_info() {
                $return = array();
                $return['log'] = 'freeCrystal_update';
                $return['progress_file'] = '/tmp/compilation_freeCrystal_in_progress';
                if (exec('dpkg -s arp-scan | grep -c "Status: install"') ==1)
                        $return['state'] = 'ok';
                else
                        $return['state'] = 'nok';
                return $return;
        }
        public static function dependancy_install() {
                if (file_exists('/tmp/compilation_freeCrystal_in_progress')) {
                        return;
                }
                log::remove('freeCrystal_update');
                $cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../ressources/install.sh';
                $cmd .= ' >> ' . log::getPathToLog('eibd_update') . ' 2>&1 &';
		exec($cmd);
        }
        public static function deamon_info() {
                $return = array();
                $return['log'] = 'freeCrystal';
                if(trim(config::byKey('DemonSleep','freeCrystal'))!='')
                        $return['launchable'] = 'ok';
                else
                        $return['launchable'] = 'nok';
                $cron = cron::byClassAndFunction('freeCrystal', 'getfreeCrystalInformation');
                if(is_object($cron) && $cron->running() )
                        $return['state'] = 'ok';
                else
                        $return['state'] = 'nok';
                return $return;
        }
        public static function deamon_start($_debug = false) {
                log::remove('freeCrystal');
                self::deamon_stop();
                $deamon_info = self::deamon_info();
                if ($deamon_info['launchable'] != 'ok')
                        return;
                if ($deamon_info['state'] == 'ok')
                        return;
                $cron = cron::byClassAndFunction('freeCrystal', 'getfreeCrystalInformation');
                if (!is_object($cron)) {
                        $cron = new cron();
                        $cron->setClass('freeCrystal');
                        $cron->setFunction('getfreeCrystalInformation');
                        $cron->setEnable(1);
                        $cron->setDeamon(1);
                        $cron->setSchedule('* * * * *');
                        $cron->setTimeout('999999');
                        $cron->save();
                }
                $cron->start();
                $cron->run();
        }
        public static function deamon_stop() {
                $cron = cron::byClassAndFunction('freeCrystal', 'getfreeCrystalInformation');
                if (is_object($cron)) {
                        $cron->stop();
                        $cron->remove();
                }
        }
        public static function AddDevice($Name,$_logicalId) {
                        $Equipement = self::byLogicalId($_logicalId, 'freeCrystal');
                        if (!is_object($Equipement)) {
                                $Equipement = new freeCrystal();
                                $Equipement->setName($Name);
                                $Equipement->setLogicalId($_logicalId);
                                $Equipement->setObject_id(null);
                                $Equipement->setEqType_name('freeCrystal');
                                $Equipement->setIsEnable(1);
                                $Equipement->setIsVisible(0);
                                $Equipement->save();
                        }
                        return $Equipement;
                }
        public static function AddCommmande($Equipement,$Name,$_logicalId, $Type="info",$SubType='string') {
                $Commande = $Equipement->getCmd(null,$_logicalId);
                if (!is_object($Commande))
                        {
                        $Commande = new freeCrystalCmd();
                        $Commande->setId(null);
                        $Commande->setName($Name);
                        $Commande->setLogicalId($_logicalId);
                        $Commande->setEqLogic_id($Equipement->getId());
                        $Commande->setType($Type);
                        $Commande->setSubType($SubType);
                        }
                $Commande->save();
                return $Commande;
                }
    public function getfreeCrystalInformation() {
            while(true){
                        $url = 'http://mafreebox.freebox.fr/pub/fbx_info.txt';
                        $tablo=file($url);
                                for($loop=0; $loop<=count($tablo); $loop++){
                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                        switch($ligne){
                                                case "Informations générales :":
                                                        $InformationsGenerales=freeCrystal::AddDevice("Informations générales","InformationsGenerales");
                                                        freeCrystal::AddCommmande($InformationsGenerales,'Redemarrage','Redemarrage', "action",'other');
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,8));
                                                        log::add('freeCrystal', 'debug', $value);
                                                        $Commande=freeCrystal::AddCommmande($InformationsGenerales,'Modèle','Modele', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,20));
                                                        $Commande=freeCrystal::AddCommmande($InformationsGenerales,'Version du firmware','VersionFirmware', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,18));
                                                        $Commande=freeCrystal::AddCommmande($InformationsGenerales,'Mode de connection','ModeConnection', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,29));
                                                        $Commande=freeCrystal::AddCommmande($InformationsGenerales,'Temps depuis la mise en route','TempsMiseRoute', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();
                                                break;
                                                case "Téléphone :":
                                                        $Telephone=freeCrystal::AddDevice("Téléphone","Telephone");
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,4));
                                                        $Commande=freeCrystal::AddCommmande($Telephone,'Etat','Etat', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,16));
                                                        $Commande=freeCrystal::AddCommmande($Telephone,'Etat du combiné','EtatCombine', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,8));
                                                        $Commande=freeCrystal::AddCommmande($Telephone,'Sonnerie','Sonnerie', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();
                                                break;
                                                case "Adsl :":
                                                        $Adsl=freeCrystal::AddDevice("Adsl","Adsl");
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,4));
                                                        $Commande=freeCrystal::AddCommmande($Adsl,'Etat','Etat', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,10));
                                                        $Commande=freeCrystal::AddCommmande($Adsl,'Protocole','Protocole', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,4));
                                                        $Commande=freeCrystal::AddCommmande($Adsl,'Mode','Mode', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $loop++;
                                                        $loop++;

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,10));
                                                        //$value=split($value);
                                                        $Commande=freeCrystal::AddCommmande($Adsl,'Débit ATM In','DebitATM In', "info",'numeric');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();
                                                        $value=trim(substr($ligne,43));
                                                        //$value=split($value);
                                                        $Commande=freeCrystal::AddCommmande($Adsl,'Débit ATM Out','DebitATM Out', "info",'numeric');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,23));
                                                        $Commande=freeCrystal::AddCommmande($Adsl,'Marge de bruit In','Marge de bruit In', "info",'numeric');
                                                        //log::add('freeCrystal', 'debug', 'Marge de bruit In  VALUE --> ' . $value);
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();
                                                        $value=trim(substr($ligne,42));
                                                        $Commande=freeCrystal::AddCommmande($Adsl,'Marge de bruit Out','Marge de bruit Out', "info",'numeric');
                                                        //log::add('freeCrystal', 'debug', 'Marge de bruit Out VALUE --> ' . $value);
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,23));
                                                        $Commande=freeCrystal::AddCommmande($Adsl,'Atténuation In','Attenuation In', "info",'numeric');
                                                        //log::add('freeCrystal', 'debug', 'Atténuation In  VALUE ' . $value);
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();
                                                        $value=trim(substr($ligne,43));
                                                        $Commande=freeCrystal::AddCommmande($Adsl,'Atténuation Out','Attenuation Out', "info",'numeric');
                                                        //log::add('freeCrystal', 'debug', 'Atténuation Out VALUE ' . $value);
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,4));
                                                        $Commande=freeCrystal::AddCommmande($Adsl,'FEC','FEC', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,4));
                                                        $Commande=freeCrystal::AddCommmande($Adsl,'CRC','CRC', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,4));
                                                        $Commande=freeCrystal::AddCommmande($Adsl,'HEC','HEC', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();
                                                break;
                                                case "Journal de connexion adsl :":
                                                        $JournalAdsl=freeCrystal::AddDevice("Journal de connexion adsl ","JournalAdsl");
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,14));
                                                        $Commande=freeCrystal::AddCommmande($JournalAdsl,'Mise en route 1','MiseRoute1', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,14));
                                                        $Commande=freeCrystal::AddCommmande($JournalAdsl,'Mise en route 2','MiseRoute2', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,14));
                                                        $Commande=freeCrystal::AddCommmande($JournalAdsl,'Mise en route 3','MiseRoute3', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();   ;
                                                break;
                                                case "Wifi :":
                                                        $Wifi=freeCrystal::AddDevice("Wifi","Wifi");
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,4));
                                                        $Commande=freeCrystal::AddCommmande($Wifi,'Etat','Etat', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,8));
                                                        $Commande=freeCrystal::AddCommmande($Wifi,'Modèle','Modele', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,6));
                                                        $Commande=freeCrystal::AddCommmande($Wifi,'Canal','Canal', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event(trim(str_replace('Canal','',utf8_encode($tablo[$loop]))));
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,16));
                                                        $Commande=freeCrystal::AddCommmande($Wifi,'État du réseau','EtatReseau', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,4));
                                                        $Commande=freeCrystal::AddCommmande($Wifi,'Ssid','Ssid', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,12));
                                                        $Commande=freeCrystal::AddCommmande($Wifi,'Type de clé','TypeCle', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,8));
                                                        $Commande=freeCrystal::AddCommmande($Wifi,'FreeWifi','FreeWifi', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,16));
                                                        $Commande=freeCrystal::AddCommmande($Wifi,'FreeWifi Secure','FreeWifiSecure', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();
                                                break;
                                                case "Réseau :":
                                                        $Reseau=freeCrystal::AddDevice("Réseau","Reseau");
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $loop++;
                                                        $loop++;

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,20));
                                                        $Commande=freeCrystal::AddCommmande($Reseau,'Adresse MAC Freebox','AdresseMACFreebox', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,10));
                                                        $Commande=freeCrystal::AddCommmande($Reseau,'Adresse IP','AdresseIP', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,5));
                                                        $Commande=freeCrystal::AddCommmande($Reseau,'IPv6','IPv6', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,12));
                                                        $Commande=freeCrystal::AddCommmande($Reseau,'Mode routeur','ModeRouteur', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        /*$value=htmlentities(trim(substr($ligne,16)));
                                                        $Commande=freeCrystal::AddCommmande($Reseau,'Adresse IP privée','AdresseIPprivee', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();   */

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,14));
                                                        $Commande=freeCrystal::AddCommmande($Reseau,'Adresse IP DMZ','AdresseIPDMZ', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,22));
                                                        $Commande=freeCrystal::AddCommmande($Reseau,'Adresse IP Freeplayer','AdresseIPFreeplayer', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,16));
                                                        $Commande=freeCrystal::AddCommmande($Reseau,'Réponse au ping','ReponsePing', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,18));
                                                        $Commande=freeCrystal::AddCommmande($Reseau,'Proxy Wake On Lan','ProxyWakeOnLan', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,12));
                                                        $Commande=freeCrystal::AddCommmande($Reseau,'Serveur DHCP','ServeurDHCP', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,26));
                                                        $Commande=freeCrystal::AddCommmande($Reseau,'Plage d\'adresses dynamique','PlageAdressesDynamique', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                /*      $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,4));
                                                        $Commande=freeCrystal::AddCommmande($Reseau,'Etat','Etat', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();   */
                                                        break;
                                                case "Attributions dhcp :" :
                                                        $DHCP=freeCrystal::AddDevice("DHCP","DHCP");
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        while(true){
                                                                log::add('freeCrystal', 'debug', strpos($ligne,'Redirections'));
                                                                log::add('freeCrystal', 'debug', $ligne);
                                                                $Mac=trim(substr($ligne,0,strpos($ligne,' ')));
                                                                $Ip=trim(substr($ligne,strpos($ligne,' ')));
                                                                if($Mac != ''){
                                                                        $Commande=freeCrystal::AddCommmande($DHCP,$Mac,str_replace(':','',$Mac), "info",'binary');
                                                                        $Commande->setConfiguration('Mac',$Mac);
                                                                        $Commande->setConfiguration('Ip',$Ip);
                                                                        $value = $Commande->getEqLogic()->MacIsConnected($Mac);
                                                                        log::add('freeCrystal','debug','Etat de '.$Commande->getName().' => '.$value);
                                                                        $Commande->setCollectDate('');
                                                                        $Commande->event($value);
                                                                        $Commande->save();
                                                                        }
                                                                $loop++;
                                                                $ligne=trim(utf8_encode($tablo[$loop]));
                                                                if (strpos($ligne,'ports')>0)
                                                                        break;
                                                        }
                                                        $loop--;
                                                break;
                                                case "Redirections de ports :":
                                                        $Redirections=freeCrystal::AddDevice("Redirections de ports","Redirections");
                                                        $RedirectionsCmd=cmd::byEqLogicId($Redirections->getId());
                                                //      foreach ($RedirectionsCmd as $Redirection)
                                                        //      $Redirection->remove();
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        while(true){
                                                                $ligne = trim($ligne);
                                                                $ligne = str_replace("\t", " ", $ligne);
                                                                $ligne = eregi_replace("[ ]+", " ", $ligne);
                                                                log::add('freeCrystal', 'debug', $ligne);
                                                                if($ligne != ''){
                                                                        $Information=split(' ',$ligne);
                                                                        log::add('freeCrystal', 'debug', count($Information));
                                                                        $Name=$Information[0].'_'.$Information[1];
                                                                        $Commande=freeCrystal::AddCommmande($Redirections,$Name,$Name, "info",'string');
                                                                        $Commande->setConfiguration('Protocole',$Information[0]);
                                                                        $Commande->setConfiguration('PortSource',$Information[1]);
                                                                        $Commande->setConfiguration('Destination',$Information[2]);
                                                                        $Commande->setConfiguration('PortDestination',$Information[3]);
                                                                        $Commande->setCollectDate('');
                                                                        $Commande->event($ligne);
                                                                        $Commande->save();
                                                                        }
                                                                $loop++;
                                                                $ligne=trim(utf8_encode($tablo[$loop]));
                                                                if (strpos($ligne,'réseau')>0)
                                                                        break;
                                                                }
                                                        $loop--;
                                                break;
                                                case "Interfaces réseau :":
                                                        $InterfacesReseau=freeCrystal::AddDevice("Interfaces réseau","InterfacesReseau");
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;
                                                        $loop++;

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,36));
                                                        $Commande=freeCrystal::AddCommmande($InterfacesReseau,'WAN In','WAN In', "info",'numeric');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $value=trim(substr($ligne,48));
                                                        $Commande=freeCrystal::AddCommmande($InterfacesReseau,'WAN Out','WAN Out', "info",'numeric');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,36));
                                                        $Commande=freeCrystal::AddCommmande($InterfacesReseau,'Ethernet In','Ethernet In', "info",'numeric');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();
                                                        //log::add('freeCrystal', 'debug', 'Ethernet In ' . $value);
                                                        $value=trim(substr($ligne,48));
                                                        $Commande=freeCrystal::AddCommmande($InterfacesReseau,'Ethernet Out','Ethernet Out', "info",'numeric');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();
                                                        //log::add('freeCrystal', 'debug', 'Ethernet Out ' . $value);

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,4));
                                                        $Commande=freeCrystal::AddCommmande($InterfacesReseau,'USB','USB', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();

                                                        $loop++;
                                                        $ligne=trim(utf8_encode($tablo[$loop]));
                                                        log::add('freeCrystal', 'debug', $ligne);
                                                        $value=trim(substr($ligne,6));
                                                        $Commande=freeCrystal::AddCommmande($InterfacesReseau,'Switch','Switch', "info",'string');
                                                        $Commande->setCollectDate('');
                                                        $Commande->event($value);
                                                        $Commande->save();
                                                break;
                                        }
                                }
                        sleep(config::byKey('DemonSleep','freeCrystal'));
                }
        }
        private function MacIsConnected($Mac) {
                $result =false;
                $cmd = 'sudo /usr/bin/arp-scan -l -g --retry=3 -T '.$Mac.' -t 500 | grep -i '.$Mac.' | wc -l 2>&1';
                //$cmd .= ' >> ' . log::getPathToLog('freeCrystal');
                exec($cmd,$result);
                //log::add('freeCrystal','debug', json_encode($result));
                return $result[0];
        }
}

class freeCrystalCmd extends cmd {

    public function dontRemoveCmd() {
        return true;
    }
    public function execute($_options = array()) {
                switch($this->getLogicalId()){
                        case 'Redemarrage':
                                if (config::byKey('Code','freeCrystal')!=''){

                                        $cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../ressources/rebootFreebox.sh';
                                        $cmd .=" hd ". config::byKey('Code','freeCrystal');
                                        $cmd .= ' >> ' . log::getPathToLog('freeCrystal') . ' 2>&1 &';
                                        exec($cmd);

                                        $cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../ressources/rebootFreebox.sh';
                                        $cmd .=" adsl ".config::byKey('Code','freeCrystal');
                                        $cmd .= ' >> ' . log::getPathToLog('freeCrystal') . ' 2>&1 &';
                                        exec($cmd);
                                }
                        break;
                }
                return $result;
    }
}

?>
