


$(".li_eqLogic").on('click', function(event) {
    printFreecrystal($(this).attr('data-eqLogic_id'));
    return false;
});
function addCmdToTable(_cmd) {
}

function printFreecrystal(_freeCrystal_id) {
    $.ajax({// fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "plugins/freeCrystal/core/ajax/freeCrystal.ajax.php", // url du fichier php
        data: {
            action: "getInformation",
            id: _freeCrystal_id
        },
        dataType: 'json',
        error: function(request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function(data) { // si l'appel a bien fonctionné
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }	
			var thead=$('<thead>');
			var tbody=$('<tbody>');
			var Type;
			switch(data.result.name)
				{
					case 'Redemarrage':
					thead.append($('<tr>')
						.append($('<th>').text('{{Nom}}'))
						.append($('<th>').text('{{Code}}'))
						.append($('<th>').text('{{Etat}}')));
					break;
					case 'DHCP':
					thead.append($('<tr>')
						.append($('<th>').text('{{Nom}}'))
						.append($('<th>').text('{{MAC}}'))
						.append($('<th>').text('{{Ip}}'))
						.append($('<th>').text('{{Etat}}')));
					break;
					case 'Réseau':
					thead.append($('<tr>')
						.append($('<th>').text('{{Nom}}'))
						.append($('<th>').text('{{Valeur}}'))
						.append($('<th>').text('{{Etat}}')));
					break;
					case 'Redirections de ports':
					thead.append($('<tr>')
						.append($('<th>').text('{{N°}}'))
						.append($('<th>').text('{{Protocole}}'))
						.append($('<th>').text('{{Port Source}}'))
						.append($('<th>').text('{{Destination}}'))
						.append($('<th>').text('{{Port Destination}}')));
					break;
					default:
					thead.append($('<tr>')
						.append($('<th>').text('{{Nom}}'))
						.append($('<th>').text('{{Valeur}}')));
					break;
				}
            for (var i in data.result.cmd) {
				switch(data.result.name)
				{
					case 'Redemarrage':
					Type="Code";
					tbody.append($('<tr>')
						.append($('<td>').text(data.result.cmd[i].name))
						.append($('<td>').append($('<input class="cmdAttr form-control input-sm" id="'+data.result.cmd[i].id+'" value="'+data.result.cmd[i].configuration.Code+'">')))
						.append($('<td>').text(data.result.cmd[i].value)));
					break;
					case 'DHCP':
					Type="Name";
					tbody.append($('<tr>')
						.append($('<td>').append($('<input class="cmdAttr form-control input-sm" id="'+data.result.cmd[i].id+'" value="' + data.result.cmd[i].name + '">')))
						.append($('<td>').text(data.result.cmd[i].configuration.Mac))
						.append($('<td>').text(data.result.cmd[i].configuration.Ip))
						.append($('<td>').text(data.result.cmd[i].value)));
					break;
					case 'Réseau':
					if (data.result.cmd[i].name =="Adresse MAC Freebox")
					{
						tbody.append($('<tr>')
							.append($('<td>').text(data.result.cmd[i].name))
							.append($('<td>').text(data.result.cmd[i].configuration.Mac))
							.append($('<td>').text(data.result.cmd[i].value)));
					}
					else
					{
						tbody.append($('<tr>')
							.append($('<td>').text(data.result.cmd[i].name))
							.append($('<td>').text(data.result.cmd[i].value))
							.append($('<td>').text('')));
					}
					break;
					case 'Redirections de ports':
					thead.append($('<tr>')
						.append($('<th>').text(data.result.cmd[i].name))
						.append($('<th>').text(data.result.cmd[i].configuration.Protocole))
						.append($('<th>').text(data.result.cmd[i].configuration.PortSource))
						.append($('<th>').text(data.result.cmd[i].configuration.Destination))
						.append($('<th>').text(data.result.cmd[i].configuration.PortDestination)));
					break;
					default:
					tbody.append($('<tr>')
						.append($('<td>').text(data.result.cmd[i].name))
						.append($('<td>').text(data.result.cmd[i].value + data.result.cmd[i].unite)));
					break;
				}
            }

		$('#table_freeCrystal').html($('<table class="table table-bordered table-condensed">')
			.append(thead)
			.append(tbody));
		$(".cmdAttr").change( function() {
		$.ajax({// fonction permettant de faire de l'ajax
			type: "POST", // methode de transmission des données au fichier php
			url: "plugins/freeCrystal/core/ajax/freeCrystal.ajax.php", // url du fichier php
			data: {
				action: "updateCommande",
				Type:Type,
				id: $(this).attr("id"),
				value: $(this).val()
			},
			dataType: 'json',
			error: function(request, status, error) {
				handleAjaxError(request, status, error);
			},
			success: function(data) { // si l'appel a bien fonctionné
			}
			});
		}); 
        }
    });
}