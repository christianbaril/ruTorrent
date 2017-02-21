<?php
/*	
	*******************************************************
	Extensions sur mesure pour ruTorrent pour configuration 
	
	Auteur 	: 	MrB
	Date 	: 	2011-01-01
	Version : 	0.0.1
	
	Description : 	Ceci est un script permettant à l'administrateur du serveur de pouvoir configurer 
					ses labels pour le classement automatisé à la suite d'un téléchargement de torrent.
					
					La configuration se situe dans le fichier configurations.txt qui consiste seulement en un fichier contenant des noms de répertoires séparés 
					par des retour de ligne \r (mac,windows,linux)
	
	Todo : 			Ajouter un étiquette à chaque torrent pour que l'administrateur puisse voir qui a chargé un torrent.
					Ajouter une interface de consultation des torrent et cumuler des statistiques

  	*******************************************************		
*/

// TODO: ajouter le user dans le champ custom4 dans rtorrent
// TODO: system.method.set_key = event.download.finished,move_complete,"d.set_directory=$d.get_custom=dir ;execute=mv,-u,$d.get_base_path=,$d.get_custom=dir"
// d.get_custom=user / d.set_custom=user,foo
// TODO: stats dans SQL

require_once( 'xmlrpc.php' );

$thePluginPath = dirname(__FILE__);
require_once($thePluginPath . '/conf.php');

class wsCustomization{
	public $hash = "wscustomization.dat";
	private $infoUtilisateur = array();
	public 	$labels = array();
		
	public static function init(){
		global $allowedLabels;		
		$adminList = Array(
							"wicked", 
							// "mrb"
					);
		$username = $_SERVER['PHP_AUTH_USER'];
		$infoUtilisateur = Array(
									"username" => $username,
									"admin" => in_array($username,$adminList)
								);

		$allowedLabels = json_encode($allowedLabels);
		if(!$infoUtilisateur["admin"]){
        // Enlever les accès
        ?>
				$(document).ready(function(){
					theWebUI.showSettings = function() {
						log('unauthorized');
						return;
					};
                    $('#t').children().each(function(i){
                        var standardButtons = new Array("mnu_start","mnu_pause","mnu_stop");
                        if ($.inArray((this).id,standardButtons) < 0){
                            $(this).remove();
                        }
                    });
				});
		<?php
		}
        // Enlever les icones inutiles pour tout le monde.
        ?>
				$(document).ready(function(){
					theWebUI.showAdd = function(){
						log('unauthorized');
						return;
					};

					$("#mnu_add,#mnu_remove,#mnu_help,.TB_Separator").remove();

					// Creation du menu contextuel
					theWebUI.createMenu = function (e, id) {
						var table = this.getTable("trt");
						theContextMenu.clear();
						
						if(table.selCount > 1) {
								theContextMenu.add([theUILang.Start,  "theWebUI.start()"]);
								theContextMenu.add([theUILang.Pause, "theWebUI.pause()"]);
								theContextMenu.add([theUILang.Stop, "theWebUI.stop()"]);
						} else {
							var canStart = $.trim(this.torrents[id].label).length > 0;
							theContextMenu.add([theUILang.Start, (this.isTorrentCommandEnabled("start",id) && canStart) ? "theWebUI.start()" : null]);
							theContextMenu.add([theUILang.Pause, (this.isTorrentCommandEnabled("pause",id) || this.isTorrentCommandEnabled("unpause",id)) ? "theWebUI.pause()" : null]);
							theContextMenu.add([theUILang.Stop, this.isTorrentCommandEnabled("stop",id) ? "theWebUI.stop()" : null]);
						}
						if(theWebUI.systemInfo.rTorrent.iVersion>=0x809)
							theContextMenu.add([theUILang.updateTracker, this.isTorrentCommandEnabled("updateTracker",id) ? "theWebUI.updateTracker()" : null]);
						theContextMenu.add([CMENU_SEP]);
						var _bf = [];
							
						for(v in allowedLabels){
							k = allowedLabels[v];
							_bf.push([k, "theWebUI.setLabel('" + addslashes(k) + "')"]);
						}
						
						theContextMenu.add([CMENU_CHILD, theUILang.Labels, _bf]);
						theContextMenu.add([CMENU_SEP]);
						<?php 
						if($infoUtilisateur["admin"]){
							?>
							theContextMenu.add([('Remove and delete data'), (table.selCount > 1) || this.isTorrentCommandEnabled("remove",id) ? "theWebUI.removeWithData()" : null]);
							<?php 
						}
						?>
						theContextMenu.add([theUILang.Remove, (table.selCount > 1) || this.isTorrentCommandEnabled("remove",id) ? "theWebUI.remove()" : null]);
					};
					
					theWebUI.setLabelOwner = function (lbl){
							var req = '';
					   		var sr = this.getTable("trt").rowSel;
							for(var k in sr){
								if(sr[k] && (this.torrents[k].label != lbl))
									req += ("&hash=" + k + "&s=label&v=" + encodeURIComponent(lbl)) + '&owner=' + encodeURIComponent(userinfos.username);;
							}
							
							if(req.length>0)
								this.request("action.php?action=setlabel"+req+"&list=1",[this.addTorrents, this]);
							
							//log('Log  blabla: ' + this.torrents[k].name + " added by " + userinfos.username);
					}
					
						
					theWebUI.perform =	function(cmd){
						if(cmd == "pause"){
							var hp = this.getHashes("unpause");
							if(hp != ""){
								var theHash = hp.split('&hash=');
								var canStart = true;
								$.each(theHash, function(k,v){
									if($.trim(v).length > 0){
										if($.trim(theWebUI.torrents[v].label).length == 0){
											canStart = false;
											log('Error, need label : ' + theWebUI.torrents[v].name);
										}
									}
								});				
								if(!canStart)
									return;
								this.request("?action=unpause" + hp);
							}
						}
						var h = this.getHashes(cmd);
						if(cmd == "start"){
							var theHash = h.split('&hash=');
							var canStart = true;
							$.each(theHash, function(k,v){
								if($.trim(v).length > 0){
									if($.trim(theWebUI.torrents[v].label).length == 0){
										canStart = false;
										log('Error, need label : ' + theWebUI.torrents[v].name);
									}
								}
							});
							if(!canStart)
								return;
						}
						
						if(h != ""){
							if((cmd.indexOf("remove")==0) && (h.indexOf(this.dID) >- 1)){
								this.dID = "";
								this.clearDetails();
							}
							this.getTorrents(cmd + h + "&list=1");
						}
						
					}
					

<?php if (!$infoUtilisateur["admin"]) {
// Si l'usager n'est pas administrateur
?>
var cols = theWebUI.getTable('trt').colsdata;
for ( var i = 0; i < cols.length; i++ ) {
    if ( cols[i].id === 'owner' ) {
        if ( cols[i].enabled ) {
            theWebUI.getTable('trt').toggleColumn(i);
        }
        break;
    }
}
var tableToggleColumn = dxSTable.prototype.toggleColumn;
dxSTable.prototype.toggleColumn = function( i ) {
    if ( this.colsdata[i].enabled || this.colsdata[i].id !== 'owner' ) {
        tableToggleColumn.call(this, i);
    }
}
dxSTable.prototype.onRightClick = function( e ) {
    if ( e.which === 3 && !this.isMoving ) {
        theContextMenu.clear();
        for ( var i = 0; i < this.colsdata.length; i++ ) {
            var data = this.colsdata[i];
            if ( this.colOrder[i] && data.id !== 'owner' ) {
                var a = [data.text, "theWebUI.getTable('"+this.prefix+"').toggleColumn("+i+")"];
                if ( data.enabled ) {
                    a.unshift(CMENU_SEL);
                }
                theContextMenu.add(a);
            }
        }
        theContextMenu.setNoHide();
        theContextMenu.show(e.clientX,e.clientY);
        return(false);
    }
}
<?php }
/*
 Envoyer la commande pour définir le propriétaire
 */
?>

var rTorrentStubStart = rTorrentStub.prototype.start;
rTorrentStub.prototype.start = function() {
    rTorrentStubStart.call(this);
    eeee = this;
    //this.contentType = "application/xml";
    //this.contentType = "application/javascript";
    for ( var i=0; i < this.hashes.length; i++ ) {
        var owner = theWebUI.getTable('trt').getValueById(this.hashes[i], 'owner');
        if ( !owner ) {
            var cmd = new rXMLRPCCommand('d.set_custom');
            cmd.addParameter('string', this.hashes[i]);
            cmd.addParameter('string', 'owner');
            cmd.addParameter('string', '<?php echo $infoUtilisateur['username']; ?>');
            this.commands.push(cmd);
        }
    }
};

				});
				

		<?php
		echo "allowedLabels = $allowedLabels;";
		echo "userinfos = " . json_encode($infoUtilisateur) . ";";
	}
	
}

?>
