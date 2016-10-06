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
		exec('sudo echo 1 > /tmp/compilation_freeCrystal_in_progress');
		$cmd = 'sudo apt-get update -y --force-yes';
		$cmd .= ' >> ' . log::getPathToLog('freeCrystal_update') . ' 2>&1 &';
		exec($cmd);
		exec('sudo echo 50 > /tmp/compilation_freeCrystal_in_progress');
		$cmd = 'sudo apt-get install  -y --force-yes arp-scan';
		$cmd .= ' >> ' . log::getPathToLog('freeCrystal_update') . ' 2>&1 &';
		exec($cmd);
		exec('sudo echo 100 > /tmp/compilation_freeCrystal_in_progress');
		exec('sudo rm /tmp/compilation_freeCrystal_in_progress');
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
	public static function AddCommmande($Equipement,$Name,$_logicalId, $Type="info",$SubType='string',$EventOnly=0) {
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
		$Commande->setIsHistorized($Equipement->getConfiguration('historize'));
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
							freeCrystal::AddCommmande($InformationsGenerales,'Redemarrage','Redemarrage', "action",'other',1);
							log::add('freeCrystal', 'debug', $ligne);
							$loop++;
							$loop++;
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,8));
							log::add('freeCrystal', 'debug', $value);
							$Commande=freeCrystal::AddCommmande($InformationsGenerales,'Modèle','Modele', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,20));
							$Commande=freeCrystal::AddCommmande($InformationsGenerales,'Version du firmware','VersionFirmware', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();
						
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,18));
							$Commande=freeCrystal::AddCommmande($InformationsGenerales,'Mode de connection','ModeConnection', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,29));
							$Commande=freeCrystal::AddCommmande($InformationsGenerales,'Temps depuis la mise en route','TempsMiseRoute', "info",'string',1);
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
							$Commande=freeCrystal::AddCommmande($Telephone,'Etat','Etat', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,16));
							$Commande=freeCrystal::AddCommmande($Telephone,'Etat du combiné','EtatCombine', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,8));
							$Commande=freeCrystal::AddCommmande($Telephone,'Sonnerie','Sonnerie', "info",'string',1);
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
							$Commande=freeCrystal::AddCommmande($Adsl,'Etat','Etat', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,10));
							$Commande=freeCrystal::AddCommmande($Adsl,'Protocole','Protocole', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,4));
							$Commande=freeCrystal::AddCommmande($Adsl,'Mode','Mode', "info",'string',1);
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
							$Commande=freeCrystal::AddCommmande($Adsl,'Débit ATM ','DebitATM', "info",'numeric',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,14));
							$Commande=freeCrystal::AddCommmande($Adsl,'Marge de bruit','MargeBruit', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,12));
							$Commande=freeCrystal::AddCommmande($Adsl,'Atténuation','Attenuation', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,4));
							$Commande=freeCrystal::AddCommmande($Adsl,'FEC','FEC', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,4));
							$Commande=freeCrystal::AddCommmande($Adsl,'CRC','CRC', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,4));
							$Commande=freeCrystal::AddCommmande($Adsl,'HEC','HEC', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();     
						break;	
						case "Journal de connexion adsl :":
							$JournalAdsl=freeCrystal::AddDevice("Journal de connexion adsl ","JournalAdsl ",1);
							log::add('freeCrystal', 'debug', $ligne);
							$loop++;
							$loop++;
							$loop++;
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,14));
							$Commande=freeCrystal::AddCommmande($JournalAdsl,'Mise en route 1','MiseRoute1', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,14));
							$Commande=freeCrystal::AddCommmande($JournalAdsl,'Mise en route 2','MiseRoute2', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();     
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,14));
							$Commande=freeCrystal::AddCommmande($JournalAdsl,'Mise en route 3','MiseRoute3', "info",'string',1);
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
							$Commande=freeCrystal::AddCommmande($Wifi,'Etat','Etat', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,8));
							$Commande=freeCrystal::AddCommmande($Wifi,'Modèle','Modele', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,6));
							$Commande=freeCrystal::AddCommmande($Wifi,'Canal','Canal', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event(trim(str_replace('Canal','',utf8_encode($tablo[$loop]))));
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,16));
							$Commande=freeCrystal::AddCommmande($Wifi,'État du réseau','EtatReseau', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,4));
							$Commande=freeCrystal::AddCommmande($Wifi,'Ssid','Ssid', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,12));
							$Commande=freeCrystal::AddCommmande($Wifi,'Type de clé','TypeCle', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,8));
							$Commande=freeCrystal::AddCommmande($Wifi,'FreeWifi','FreeWifi', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,16));
							$Commande=freeCrystal::AddCommmande($Wifi,'FreeWifi Secure','FreeWifiSecure', "info",'string',1);
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
							$Commande=freeCrystal::AddCommmande($Reseau,'Adresse MAC Freebox','AdresseMACFreebox', "info",'string',0);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,10));
							$Commande=freeCrystal::AddCommmande($Reseau,'Adresse IP','AdresseIP', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,5));
							$Commande=freeCrystal::AddCommmande($Reseau,'IPv6','IPv6', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,12));
							$Commande=freeCrystal::AddCommmande($Reseau,'Mode routeur','ModeRouteur', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,16));
							$Commande=freeCrystal::AddCommmande($Reseau,'Adresse IP privée','AdresseIPprivee', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,14));
							$Commande=freeCrystal::AddCommmande($Reseau,'Adresse IP DMZ','AdresseIPDMZ', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,22));
							$Commande=freeCrystal::AddCommmande($Reseau,'Adresse IP Freeplayer','AdresseIPFreeplayer', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,16));
							$Commande=freeCrystal::AddCommmande($Reseau,'Réponse au ping','RéponsePing', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,18));
							$Commande=freeCrystal::AddCommmande($Reseau,'Proxy Wake On Lan','ProxyWakeOnLan', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,12));
							$Commande=freeCrystal::AddCommmande($Reseau,'Serveur DHCP','ServeurDHCP', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,26));
							$Commande=freeCrystal::AddCommmande($Reseau,'Plage d\'adresses dynamique','PlageAdressesDynamique', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							
						/*	$loop++;
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
									$Commande=freeCrystal::AddCommmande($DHCP,$Mac,str_replace(':','',$Mac), "info",'binary',0);	
									$Commande->setConfiguration('Mac',$Mac);
									$Commande->setConfiguration('Ip',$Ip);
									$value = $Commande->getEqLogic()->MacIsConnected($Mac);
									log::add('freeCrystal','debug','Etat de '.$Commande->getName().': '.$value);
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
						//	foreach ($RedirectionsCmd as $Redirection)
							//	$Redirection->remove();
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
									$Commande=freeCrystal::AddCommmande($Redirections,$Name,$Name, "info",'string',0);
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
							$value=trim(substr($ligne,4));
							$Commande=freeCrystal::AddCommmande($InterfacesReseau,'WAN','WAN', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();  
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,8));
							$Commande=freeCrystal::AddCommmande($InterfacesReseau,'Ethernet','Ethernet', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,4));
							$Commande=freeCrystal::AddCommmande($InterfacesReseau,'USB','USB', "info",'string',1);
							$Commande->setCollectDate('');
							$Commande->event($value);
							$Commande->save();   
							$loop++;
							$ligne=trim(utf8_encode($tablo[$loop]));
							log::add('freeCrystal', 'debug', $ligne);
							$value=trim(substr($ligne,6));
							$Commande=freeCrystal::AddCommmande($InterfacesReseau,'Switch','Switch', "info",'string',1);
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
		$cmd = 'sudo /usr/bin/arp-scan -l -g --retry=5 -T '.$Mac.' -t 800 | grep -i '.$Mac.' | wc -l';
		$cmd .= ' >> ' . log::getPathToLog('freeCrystal');
		$result = trim(exec($cmd));
		return $result;
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
					$request='cd '.dirname(__FILE__).'/../../ressources/ && ';
					$request.='./rebootFreebox.sh';
					if($this->getLogicalId()=='Serveur')
						$request.=" adsl ".$this->getConfiguration('Code');
					if($this->getLogicalId()=='Player')
						$request.=" hd ".$this->getConfiguration('Code');
					$request_shell = new com_shell($request . ' 2>&1');  
					$result = trim($request_shell->exec());
				}
			break;
		}
		return $result;
    }
}

?>
