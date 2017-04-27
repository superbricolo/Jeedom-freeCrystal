<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>
<form class="form-horizontal">
    <fieldset>
      <div class="form-group">
         <label class="col-lg-4 control-label">Délais de rafraîchissement des informations (s) :</label>
         <div class="col-lg-4">
            <input class="configKey form-control" data-l1key="DemonSleep"/>
         </div>
      </div> 
      <div class="form-group">
         <label class="col-lg-4 control-label">Code télécommande :</label>
         <div class="col-lg-4">
            <input class="configKey form-control" data-l1key="Code"/>
         </div>
      </div> 
      <div class="form-group">
         <label class="col-lg-4 control-label">Scan DHCP :</label>
         <div class="col-lg-4">
            <input type="checkbox" class="configKey" data-l1key="ScanDHCP"/>
         </div>
      </div>   
   </fieldset>
</form>
