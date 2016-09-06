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
			<label class="col-lg-4 control-label">Rafraichissement des informations Principal :</label>
			<div class="col-lg-4">
				<input class="configKey form-control" data-l1key="RefreshData" id="RefreshData"/>
				<a class="btn btn-default btn-xs cursor" style="position : relative; top : 3px;" title="Générateur Cron">
					<i class="fa fa-list-alt"></i>
				</a>
			</div>
		</div> 
   </fieldset>
</form>
<script>
$(function() 
	{
  $('.form-group').delegate('.btn', 'click', function() {
	  	var _this=this;
		jeedom.getCronSelectModal({},function (result) {
			$('#RefreshData').val(result.value);
			}); 
		}); 
	});
</script>
