<!DOCTYPE html>
<html>
	<head>
		<title>Critical Path Method</title>
		<meta charset="UTF-8"/>
		<link rel="stylesheet" type="text/css" href="cpm.css">
		<link rel="styleSheet" type="text/css" href="bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="icon/css/font-awesome.min.css">
	</head>

	<body style="background-color: rgb(220,220,220)">
		<div>
			<div>
				<div><p id="notif"></p></div>
				<div><h3 id="grand_titre">Ordonnancement des tâches CPM (Critical Path Method)</h3></div>

				<div class="marge">
					<table id="tableInitial">
						<tbody><tr id="ligne_tache"> <td style="width:200px" class="table_title">Tâches</td><td class="cell">a</td><td class="cell">b</td><td class="cell">c</td><td class="cell">d</td><td class="cell">e</td></tr>
						<tr id="ligne_duree"><td style="width:200px" class="table_title">Durée</td><td class="cell">1</td><td class="cell">2</td><td class="cell">3</td><td class="cell">4</td><td class="cell">5</td></tr>
						<tr id="ligne_anterieure"><td style="width:200px" class="table_title">Tâches antérieures</td><td class="cell">-</td><td class="cell">a</td><td class="cell">b</td><td class="cell">b</td><td class="cell">c</td></tr>
					</tbody></table>
				</div>

				<div class="marge">
					<button id="btn_add_column" class="btn btn-primary"> <i class="fa fa-plus"></i> Ajouter colonne</button>
					<button id="btn_remove_column" class="btn btn-danger"> <i class="fa fa-minus"></i> Enlever colonne</button>
					<button id="btn_go" class="btn btn-success"> <i class="fa fa-play"></i> Lancer</button>
					<button id="btn_replay" class="btn btn-success"> <i class="fa fa-repeat"></i> Rejouer</button>
				</div>

				<div>
					<h6 class="titre_action"></h6>
				</div>

				<div>
					<canvas id="canvas" width="4000" height="1500">
						
					</canvas>
				</div>
			</div>
		</div>

		<script src="jQuery-2.1.4.min.js"></script>
		<script scr="bootstrap.min.js"></script>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				$('#btn_go').hide();
				$('#btn_replay').hide();
				$('#notif').hide();
				var r = 19, interval, compteDateTot=0;
				var tache_array = new Array(), anterieure_array = new Array(), tabObject = new Array();

				$('#btn_add_column').on('click',function(){
					var innerTacheLine = $('#ligne_tache').html();
					var innerDureeLine = $('#ligne_duree').html();
					var innerAnterieureLine = $('#ligne_anterieure').html();
					
					$('#ligne_tache').html(innerTacheLine+'<td class="cell"></td>');
					$('#ligne_duree').html(innerDureeLine+'<td class="cell"></td>');
					$('#ligne_anterieure').html(innerAnterieureLine+'<td class="cell"></td>');

					$('.cell').on('dblclick',function(e){
						var getCellValue = $(this).text();
						$(this).html('<input type="text" value="'+getCellValue+'" class="textZone" style="width:125px;height:10px;">');
						$(this).children('input').focus();
						$('.textZone').on('focusout',function(){
							var inputValue = $(this).val();
							$(this).parent().html(inputValue);
						});
					});
					if($('#ligne_tache').children().length >= 2){
						$('#btn_go').show();
					}
				});

				$('#btn_remove_column').on('click',function(){
					var childrenTacheLine = $('#ligne_tache').children();
					var childrenDureeLine = $('#ligne_duree').children();
					var childrenAnterieureLine = $('#ligne_anterieure').children();

					var columnCount = childrenTacheLine.length;
					//Si le nombre de colonne est supérieur à un on peut enlever une colonne
					if (columnCount>1){
						childrenTacheLine[columnCount-1].remove();
						childrenDureeLine[columnCount-1].remove();
						childrenAnterieureLine[columnCount-1].remove();
					}
					if($('#ligne_tache').children().length <= 1){
						$('#btn_go').hide();
					}
				});
				
				$('#btn_go').on('click',function(){
					var vide = caseVide();
					if(vide){
						$('#notif').show();
						$('#notif').html('Tableau incomplet. Veuillez remplir le tableau');
					}
					else{
						getSuccesseure();
						ligneComplet();
						interval = setInterval(dessiner,1000);
						$('#notif').fadeIn('slow',function(){
							var timeout = setTimeout(function() {
								$('#notif').hide();
								clearTimeout(timeout);
							}, 2000);
						});
					}
				});

				$('#btn_replay').on('click', function(){
					var vide = caseVide();
					if(vide){
						$('#notif').show();
						$('#notif').html('Tableau incomplet. Veuillez remplir le tableau');
					}
					else{
						gommerCanvas();
						interval = setInterval(dessiner,1000);
						$('#notif').fadeIn('slow',function(){
							var timeout = setTimeout(function() {
								$('#notif').hide();
								clearTimeout(timeout);
							}, 2000);
						});
					}
				});

				function caseVide(){
					var childrenTacheLine = $('#ligne_tache').children();
					var childrenDureeLine = $('#ligne_duree').children();
					var childrenAnterieureLine = $('#ligne_anterieure').children();
					var test = false;
					var columnCount = childrenTacheLine.length;
					
					while(columnCount != 1){
						if((childrenTacheLine[columnCount-1].innerHTML == "") || (childrenDureeLine[columnCount-1].innerHTML == "") || (childrenAnterieureLine[columnCount-1].innerHTML == "")){
							test = true;
							return test;
							alert('case vide');
						}
						columnCount--;
					}
					return test;
				}

				function remplirTableauTache(){
					var childrenTacheLine = $('#ligne_tache').children();

					for(var i=1, nbr_tache = childrenTacheLine.length ; i<nbr_tache ; i++){
						tache_array[i-1] = childrenTacheLine[i].innerHTML;
					}
				}

				function remplirTableauAnterieure(){
					var childrenAnterieureLine = $('#ligne_anterieure').children();

					for(var i=1, nbr_tache_anterieure = childrenAnterieureLine.length ; i<nbr_tache_anterieure ; i++){
						anterieure_array[i-1] = childrenAnterieureLine[i].innerHTML;
					}
				}

				function getSuccesseure(){
					var tableauInitial = document.querySelector('#tableInitial tbody');
					tableauInitial.removeChild(tableauInitial.lastChild);
					var newSuccesseureLine = document.createElement('tr');
					var firstSuccesseureColumn = document.createElement('td');
					var firstTextSuccesseurColumn = document.createTextNode('Tâches successeures');
					newSuccesseureLine.setAttribute('id','ligne_successeure');

					firstSuccesseureColumn.appendChild(firstTextSuccesseurColumn);
					newSuccesseureLine.appendChild(firstSuccesseureColumn);
					tableauInitial.appendChild(newSuccesseureLine);

					remplirTableauTache();
					remplirTableauAnterieure();

					for(var indiceTache in tache_array){
						var tacheArechercher = tache_array[indiceTache];
						tacheArechercher.toString();
						var indiceTrouve = new Array();

						for(var indiceAnterieure in anterieure_array){
							var columnAnterieureValue = anterieure_array[indiceAnterieure];
							columnAnterieureValue.toString();
							var extractAnterieureValue = columnAnterieureValue.split(',');
							
							for(var i in extractAnterieureValue){
								if(tacheArechercher == extractAnterieureValue[i]){
									indiceTrouve.push(indiceAnterieure);
								}
							}
						}
						var successeure_array = new Array();
						for(var i in indiceTrouve){
							var tacheIndex = indiceTrouve[i];
							var tacheTrouve = tache_array[tacheIndex];
							tacheTrouve.toString();
							successeure_array.push(tacheTrouve);
						}
						var allTache = successeure_array.join(',');
						allTache.toString();
						if(allTache == ""){
							allTache = "FIN";
							var newSuccesseurColumn = document.createElement('td');
							var newTextColumn = document.createTextNode(allTache);

							newSuccesseurColumn.appendChild(newTextColumn);
							$('#ligne_successeure').append(newSuccesseurColumn);
						}
						else{
							var newSuccesseurColumn = document.createElement('td');
							var newTextColumn = document.createTextNode(allTache);

							newSuccesseurColumn.appendChild(newTextColumn);
							$('#ligne_successeure').append(newSuccesseurColumn);
						}
					}
				}

				function TacheObject(nomTache,dureeTache,anterieure,successeure,nbrPrec,bcpPreced){
					this.nomTache = nomTache;
					this.dureeTache = dureeTache;
					this.precedent = anterieure;
					this.suivant = successeure;
					this.marque = false;
					this.xf = '';
					this.yf = '';
					this.xd = '';
					this.yd = '';
					this.dateTot = 0;
					this.dateTard = 0;
					this.margeDeRetard = 0;
					this.nbrpreced = nbrPrec;
					this.bcpPreced = bcpPreced;
				}

				function TacheDebutObject(successeure){
					this.nomTache = '-';
					this.suivant = successeure;
					this.marque = false;
					this.xf = '';
					this.yf = '';
					this.dateTot = 0;
					this.dateTard = 0;
					this.margeDeRetard = 0;
					this.nbrpreced = 1;
				}

				function cercle(x,y,text){
					var canvas = document.querySelector('#canvas');
					var context = canvas.getContext('2d');
					var xText, yText;
					xText = x-7;
					yText = y+5;

					context.beginPath();
					context.arc(x, y, 19, 0, (Math.PI) * 2);
					context.stroke();
					context.font = "18px Calibri,Arial";
					context.fillStyle = "rgb(23, 145, 167)";
					context.fillText(text, xText, yText);
				}

				function flecheHorizontale(xdeb,xfin,y,t,tacheText,dureeText){
					var canvas = document.querySelector('#canvas');
					var context = canvas.getContext('2d');
					context.fillStyle = "rgba(225,0,0,1)"; 
					context.strokeStyle = "rgba(225,0,0,1)";
					context.beginPath();
					context.moveTo(xdeb,y);
					context.lineTo(xfin,y);
					context.closePath();
					context.stroke();
					context.beginPath();
					context.moveTo(xfin,y);
					context.lineTo(xfin-t,y+t);
					context.lineTo(xfin-t,y-t);
					context.fill();
					context.fillStyle = "rgba(0,0,0,1)"; 
					context.strokeStyle = "rgba(0,0,0,1)";
					context.font = "15px Calibri,Geneva,Arial";
					xText = xdeb+((xfin-xdeb)/2);
					context.fillText(tacheText,xText,y-7);
					context.fillText(dureeText,xText,y+17);
				}

				function flecheCourbe(xd,yd,xf,yf,tacheText,dureeText,t){
					var canvas = document.querySelector('#canvas');
					var context = canvas.getContext('2d');
					context.fillStyle = "rgba(225,0,0,1)"; 
					context.strokeStyle = "rgba(225,0,0,1)";
					context.beginPath(); 
					context.moveTo(xd,yd);
					context.quadraticCurveTo(xd,yf,xf,yf);
					context.stroke();
					context.beginPath();
					context.moveTo(xf,yf);
					context.lineTo(xf-t,yf+t);
					context.lineTo(xf-t,yf-t);
					context.fill();
					context.fillStyle = "rgba(0,0,0,1)"; 
					context.strokeStyle = "rgba(0,0,0,1)";
					context.font = "15px Calibri,Geneva,Arial";
					xText = xd+((xf-xd)/2);
					context.fillText(tacheText,xText,yf-7);
					context.fillText(dureeText,xText,yf+17);
				}

				function courbeBezier(xd,yd,xf,yf,tacheText,dureeText,t){
					var canvas = document.querySelector('#canvas');
					var context = canvas.getContext('2d');
					context.fillStyle = "rgba(225,0,0,1)"; 
					context.strokeStyle = "rgba(225,0,0,1)";
					context.beginPath();
					context.moveTo(xd,yd);
					var xb1, yb1, xb2, yb2;
					xb1 = xd+((xf-xd)/3);
					xb2 = xb1+((xf-xd)/3);
					if(yd < yf){
						yb1 = yd+((yf-yd)/3);
						yb2 = yb1+((yf-yd)/3);
						context.quadraticCurveTo(xb2+30,yb2-43,xf+10,yf-r);
						context.stroke();
						context.beginPath();
						var x = xf+10, y = yf-r;
						context.moveTo(x,y);
						context.lineTo(xf,y+5);
						context.lineTo(xf+15,y-10);
						context.fill();
						context.fillStyle = "rgba(0,0,0,1)"; 
						context.strokeStyle = "rgba(0,0,0,1)";
						context.font = "15px Calibri,Geneva,Arial";
						context.fillText(tacheText,xb2+30,yb2-30);
						context.fillText(dureeText,xb2+30,yb2-5);
					}
					else if(yd > yf){
						yb1 = yd-((yd-yf)/3);
						yb2 = yb1-((yd-yf)/3);
						context.quadraticCurveTo(xb2+30,yb2+43,xf+10,yf+r);
						context.stroke();
						context.beginPath();
						var x = xf+10,y = yf+r;
						context.moveTo(x,y);
						context.lineTo(xf,y-5);
						context.lineTo(xf+15,y+10);
						context.fill();
						context.fillStyle = "rgba(0,0,0,1)"; 
						context.strokeStyle = "rgba(0,0,0,1)";
						context.font = "15px Calibri,Geneva,Arial";
						context.fillText(tacheText,xb2+30,yb2+16);
						context.fillText(dureeText,xb2+30,yb2+45);
					}
					else{
						flecheCourbe(xd,yd,xf,yf,tacheText,dureeText,t);
					}
				}

				function rectangleDateTot(xC,yC,dateTot){
					var canvas = document.querySelector('#canvas');
					var context = canvas.getContext('2d');
					var xR = xC;
					var yR = yC-(r*2);
					context.strokeRect(xR,yR,r*2,r);
					context.font = "15px Calibri,Geneva,Arial";
					context.fillText(dateTot,xR+8,yR+15);
				}

				function effacerRactangleDateTot(xC,yC){
					var canvas = document.querySelector('#canvas');
					var context = canvas.getContext('2d');
					var xR = xC;
					var yR = yC-(r*2);
					context.clearRect(xR,yR,r*2,r);
				}

				function cercleRouge(xC,yC){
					var canvas = document.querySelector('#canvas');
					var context = canvas.getContext('2d');
					context.fillStyle = "rgba(225,0,0,0.2)";
					context.beginPath();
					context.arc(xC, yC, 19, 0, (Math.PI) * 2);
					context.fill();
				}

				function rectangleDateTard(xC,yC,dateTard){
					var canvas = document.querySelector('#canvas');
					var context = canvas.getContext('2d');
					var xR = xC+(r*2);
					var yR = yC-(r*2);
					context.strokeRect(xR,yR,r*2,r);
					context.font = "15px Calibri,Geneva,Arial";
					context.fillStyle = "rgb(23, 145, 167)";
					context.fillText(dateTard,xR+8,yR+15);
				}

				function rectangleMargeRetard(xC,yC,margeRetard){
					var canvas = document.querySelector('#canvas');
					var context = canvas.getContext('2d');
					var xR = xC+r;
					var yR = yC-(r*3);
					context.strokeRect(xR,yR,r*2,r);
					context.font = "15px Calibri,Geneva,Arial";
					context.fillStyle = "rgb(34, 179, 78)";
					context.fillText(margeRetard,xR+8,yR+15);
				}

				function gommerCanvas(){
					var canvas = document.querySelector('#canvas');
					var context = canvas.getContext('2d');
					context.clearRect(0,0,2000,900);
				}

				function ligneComplet(){
					var tache = new Array(); var duree = new Array(); var preced = new Array(); var succ = new Array();
					
					//toutes les taches dans un tableau y compris "-"
					var childrenTacheLine = $('#ligne_tache').children();
					for(var i=1, nbr_tache = childrenTacheLine.length ; i<nbr_tache ; i++){
						tache[i-1] = childrenTacheLine[i].innerHTML;
					}
					tache.unshift('-');
					tache.push('FIN');

					//toutes les durées -> 0 pour la première tache "-"
					var childrenDureeLine = $('#ligne_duree').children();
					for(var i=1, nbr_duree = childrenDureeLine.length ; i<nbr_duree ; i++){
						duree[i-1] = childrenDureeLine[i].innerHTML;
					}
					duree.unshift('0');
					duree.push('0');
					
					//toutes les taches successeures de la tache début "-"
					var tacheDeb = new Array();
					var tacheArechercher = '-';
					tacheArechercher.toString();
					for (var k = 0; k < anterieure_array.length; k++) {
						if(tacheArechercher == anterieure_array[k]){
							tacheDeb.push(tache_array[k]);
						}
					}
					var ligne_successeure = $('#ligne_successeure').children();
					for(var i=1 ; i<ligne_successeure.length ; i++){
						succ[i-1] = ligne_successeure[i].innerHTML;
					}
					succ.unshift(tacheDeb.join(','));
					succ.push(null);

					//Recherche des taches antérieures de la FIN
					var tabAnterieureFin = new Array();
					for(var i=0 ; i < succ.length ; i++){
						if(succ[i] == 'FIN'){
							tabAnterieureFin.push(tache[i]);
						}
					}
					var anterieureFin = tabAnterieureFin.join(',');
					
					//Les taches antérieures -> nulle pour la tache du début "-"
					for (var i = 0; i < anterieure_array.length; i++) {
						preced[i] = anterieure_array[i];
					};
					preced.unshift(null);
					preced.push(anterieureFin);
					
					//Création de la table d'objet "tabObject[]"
					for (var i = 0; i < tache.length; i++) {
						name = tache[i];
						duration = duree[i];
						anter = preced[i];
						nbrPrec = 1;
						bcpPreced = false;
						if(anter != null){
							var tabAnter = anter.split(',');
							nbrPrec = tabAnter.length;
							if(nbrPrec > 1) bcpPreced = true;
						}
						suc = succ[i];
						tabObject.push(new TacheObject(name,duration,anter,suc,nbrPrec,bcpPreced));
					}
				}

				function tacheDejaMarque(tache){
					for(var i=0 ; i < tabObject.length ; i++){
						if(tabObject[i].nomTache == tache){
							if(tabObject[i].marque){
								return true;
							}
							else{
								return false;
							}
						}
					}
				}

				function marquerTache(tache){
					for(var i=0 ; i < tabObject.length ; i++){
						if(tabObject[i].nomTache == tache){
							tabObject[i].marque=true;
							//alert('tache '+tache+' marquée');
						}
					}
				}

				function dureeCurrentTache(tache){
					for(var i=0 ; i < tabObject.length ; i++){
						if(tabObject[i].nomTache == tache){
							return tabObject[i].dureeTache;
						}
					}
				}

				function positionFinale(tache,xd,yd,xf,yf){
					for(var i=0 ; i < tabObject.length ; i++){
						if(tabObject[i].nomTache == tache){
							tabObject[i].xf = xf;
							tabObject[i].yf = yf;
							tabObject[i].yd = yd;
							tabObject[i].xd = xd;
						}
					}
				}

				function testBcpPreced(tache){
					for(var i=0 ; i < tabObject.length ; i++){
						if(tabObject[i].nomTache == tache){
							return tabObject[i].bcpPreced;
						}
					}
				}

				//Trouver position maximale de X dans canvas
				function getXmax(){
					var max = tabObject[0].xf;
					for(var i=0 ; i < tabObject.length ; i++){
						if(tabObject[i].xf > max){
							max = tabObject[i].xf;
						}
					}
					return max;
				}
				var comptdessiner = 0;
				var numTache = 1;

				function dessiner(){
					$('.titre_action').html('Succession des tâches');
					$('#btn_go').hide();
					var xDepart = 100;
					var yDepart = 300;
					var interv = 90;
					var nomDebut = tabObject[0].nomTache;
					var succDebut = tabObject[0].suivant;
					var tabSuccDebut = succDebut.split(',');
					//alert(tabObject.length);
					tabObject[0].xf = xDepart;
					tabObject[0].yf = yDepart;
					tabObject[0].marque = true;
										
					//Dessiner de la tache du début
					var tacheDebut = new TacheDebutObject(succDebut);
					cercle(xDepart,yDepart,'d');

					//Mettre les successeures de la tache du début comme non-marqué
					for(var i=0 ; i < tabSuccDebut.length ; i++){
						for(var j=0 ; j < tabObject.length ; j++){
							if(tabObject[j].nomTache == tabSuccDebut[i]){
								tabObject[j].marque = false;
							}
						}
					}

					var i=comptdessiner, tabObjectCompte = tabObject.length, tacheSucc, tabTacheSucc, xd, yd, xdFleche, ydFleche, xfFleche, yfFleche, xC, yC, dureeTacheCourante, bcpPreced, tacheMarque, currentTacheSucc, dureeAnterieure;
					
					//Parcourir le tableau d'objet tabObject[] et placer toutes les taches
				
						tacheSucc = tabObject[i].suivant;
						tabTacheSucc = tacheSucc.split(',');
						tacheSuccCompte = tabTacheSucc.length;
						var nbrTacheAplacer = parseInt(tacheSuccCompte);
						var j=0, parite;
						
						for(var c=0 ; c < tacheSuccCompte ; c++){
							for(var d=0 ; d < tabObject.length ; d++){
								if(tabTacheSucc[c] == tabObject[d].nomTache){
									if(tabObject[d].marque){
										nbrTacheAplacer = nbrTacheAplacer-1;
									}
								}
							}
						}
						
						//Position de la tache courante
						xd = tabObject[i].xf;
						yd = tabObject[i].yf;
						//Durée de la tache en cours
						dureeAnterieure = tabObject[i].dureeTache;

						var tabPositionY = new Array();
						parite = nbrTacheAplacer%2;
						var yBas, yHaut, test=true;

						//Placer les taches successeures de l'item courant de tabObject ==> Monde des taches succésseures
						while(j < tacheSuccCompte){						
							currentTacheSucc = tabTacheSucc[j];
							tacheMarque = tacheDejaMarque(currentTacheSucc);
							bcpPreced = testBcpPreced(currentTacheSucc);
							
							//Si la tache successeure n'est pas encore placée et le nombre de tache à placer n'est pas égale à 1 et le nombre de tache est impaire dans le but de tracer des flèches paraboles
							if((!tacheMarque) && (nbrTacheAplacer > 1) && (parite != 0) && (!bcpPreced)){
								while(test){
									yBas = yd+(((nbrTacheAplacer-1)/2)*interv);
									yHaut = yd-(yBas-yd);
									test = false;
								}

								xdFleche = xd+r;
								ydFleche = yd;
								xfFleche = xdFleche+100;
								yfFleche = yHaut;
								xC = xfFleche+r;
								yC = yfFleche;
								dureeTacheCourante = dureeCurrentTache(currentTacheSucc);
								flecheCourbe(xdFleche,ydFleche,xfFleche,yfFleche,currentTacheSucc,dureeTacheCourante,7);
								cercle(xC,yC,numTache);
								marquerTache(currentTacheSucc);
								positionFinale(currentTacheSucc,xd,yd,xC,yC);
								numTache = numTache+1;
								//Position Y de la tache suivante à placer, séparée entre elle par une intervalle
								yHaut = yHaut+interv;
							}
							//sinon s'il n'y a qu'une seule tache à placer
							else if((!tacheMarque) && (nbrTacheAplacer == 1) && (!bcpPreced)){
								if(currentTacheSucc == 'FIN'){
									xdFleche = xd+r;
									xfFleche = xdFleche+400;
									yfFleche = yd;
									xC = xfFleche+r;
									yC = yfFleche;
									dureeTacheCourante = dureeCurrentTache(currentTacheSucc);
									flecheHorizontale(xdFleche,xfFleche,yfFleche,7,'',dureeTacheCourante);
									cercle(xC,yC,'fin');
									marquerTache(currentTacheSucc);
									positionFinale(currentTacheSucc,xC,yC,xC,yC);
								}
								else{
									xdFleche = xd+r;
									xfFleche = xdFleche+100;
									yfFleche = yd;
									xC = xfFleche+r;
									yC = yfFleche;
									dureeTacheCourante = dureeCurrentTache(currentTacheSucc);
									flecheHorizontale(xdFleche,xfFleche,yfFleche,7,currentTacheSucc,dureeTacheCourante);
									cercle(xC,yC,numTache);
									marquerTache(currentTacheSucc);
									positionFinale(currentTacheSucc,xd,yd,xC,yC);
									numTache = numTache+1;
								}	
							}
							//sinon si le nombre de tache à placer est paire ==> la position de chaque tahe sera différente de celui de nombre de tache égal à impaire
							else if((!tacheMarque) && (nbrTacheAplacer > 1) && (parite==0) && (!bcpPreced)){
								while(test){
									yBas = yd+((((nbrTacheAplacer/2)-1)*interv)+(interv/2));
									yHaut = yd-(yBas-yd);
									test = false;
								}
								xdFleche = xd+r;
								ydFleche = yd;
								xfFleche = xdFleche+100;
								yfFleche = yHaut;
								xC = xfFleche+r;
								yC = yfFleche;
								dureeTacheCourante = dureeCurrentTache(currentTacheSucc);
								flecheCourbe(xdFleche,ydFleche,xfFleche,yfFleche,currentTacheSucc,dureeTacheCourante,7);
								cercle(xC,yC,numTache);
								marquerTache(currentTacheSucc);
								positionFinale(currentTacheSucc,xd,yd,xC,yC);
								numTache = numTache+1;
								//Position Y de la tache suivante à placer, séparée entre elle par une intervalle
								yHaut = yHaut+interv;
							}
							else if((!tacheMarque) && (bcpPreced) && (currentTacheSucc != 'FIN')){
								for(var x=0 ; x < tabObject.length ; x++){
									if(tabObject[x].nomTache == currentTacheSucc){
										var nbrAnt = parseInt(tabObject[x].nbrpreced);
									}
								}
								if(nbrAnt != 1){	
									var nouvNbrAnt = parseInt(nbrAnt)-1;
									for(var y=0 ; y < tabObject.length ; y++){
										if(tabObject[y].nomTache == currentTacheSucc){
											tabObject[y].nbrpreced = nouvNbrAnt;
										}
									}
								}
								else{
									var xMax = getXmax();
									var tabY = new Array();
									for(var z=0 ; z < tabObject.length ; z++){
										//recuperation de la position y de toutes les taches anterieures et mettre dans le tableau tabY
										if(tabObject[z].nomTache == currentTacheSucc){
											var anteri = tabObject[z].precedent;
											var tabAnteri = anteri.split(',');
											for(var min=0 ; min < tabAnteri.length ; min++){
												for(var b=0 ; b < tabObject.length ; b++){
													if(tabObject[b].nomTache == tabAnteri[min]){
														tabY.push(tabObject[b].yf);
													}
												}
											}

											var ymin= parseInt(tabY[0]);
											for(var e=1 ; e < tabY.length ; e++){
												var currentTabY = parseInt(tabY[e]);
												if(currentTabY < ymin){
													ymin = currentTabY;
												}
											}
											var ymax = parseInt(tabY[0]);
											for(var e=1 ; e < tabY.length ; e++){
												var currentTabY = parseInt(tabY[e]);
												if(currentTabY > ymax){
													ymax = currentTabY;
												}
											}
										}
									}
									yC = ymin+((ymax-ymin)/2);
									xC = xMax+r+150;
									dureeTacheCourante = dureeCurrentTache(currentTacheSucc);
									for(var z=0 ; z < tabObject.length ; z++){
										if(tabObject[z].nomTache == currentTacheSucc){
											var anteri = tabObject[z].precedent;
											var tabAnteri = anteri.split(',');
											for(var m=0 ; m < tabAnteri.length ; m++){
												for(var n=0 ; n < tabObject.length ; n++){
													if(tabObject[n].nomTache == tabAnteri[m]){
														var xd = parseInt(tabObject[n].xf);
														var yd = parseInt(tabObject[n].yf);
														var xdFleche = xd+r;
														var ydFleche =  yd;
														var xfFleche = xC-r;
														var yfFleche = yC;
														//alert('xC= '+xC+' yC= '+yC);
														courbeBezier(xdFleche,ydFleche,xfFleche,yfFleche,currentTacheSucc,dureeTacheCourante,7);
														
													}
												}
											}
										}
									}
									//alert('cercle');
									cercle(xC,yC,numTache);
									marquerTache(currentTacheSucc);
									positionFinale(currentTacheSucc,xd,yd,xC,yC);
									numTache = numTache+1;
								}
							}
							else if((!tacheMarque) && (bcpPreced) && (currentTacheSucc == 'FIN')){
								for(var x=0 ; x < tabObject.length ; x++){
									if(tabObject[x].nomTache == currentTacheSucc){
										var nbrAnt = parseInt(tabObject[x].nbrpreced);
									}
								}
								if(nbrAnt != 1){	
									var nouvNbrAnt = parseInt(nbrAnt)-1;
									for(var y=0 ; y < tabObject.length ; y++){
										if(tabObject[y].nomTache == currentTacheSucc){
											tabObject[y].nbrpreced = nouvNbrAnt;
										}
									}
								}
								else{
									var xMax = getXmax();
									var tabY = new Array();
									for(var z=0 ; z < tabObject.length ; z++){
										if(tabObject[z].nomTache == currentTacheSucc){
											var anteri = tabObject[z].precedent;
											var tabAnteri = anteri.split(',');
											for(var min=0 ; min < tabAnteri.length ; min++){
												for(var b=0 ; b < tabObject.length ; b++){
													if(tabObject[b].nomTache == tabAnteri[min]){
														tabY.push(tabObject[b].yf);
													}
												}
											}

											var ymin= parseInt(tabY[0]);
											for(var e=1 ; e < tabY.length ; e++){
												var currentTabY = parseInt(tabY[e]);
												if(currentTabY < ymin){
													ymin = currentTabY;
												}
											}
											var ymax = parseInt(tabY[0]);
											for(var e=1 ; e < tabY.length ; e++){
												var currentTabY = parseInt(tabY[e]);
												if(currentTabY > ymax){
													ymax = currentTabY;
												}
											}
										}
									}
									yC = ymin+((ymax-ymin)/2);
									xC = xMax+150;
									dureeTacheCourante = dureeCurrentTache(currentTacheSucc);
									for(var z=0 ; z < tabObject.length ; z++){
										if(tabObject[z].nomTache == currentTacheSucc){
											var anteri = tabObject[z].precedent;
											var tabAnteri = anteri.split(',');
											for(var m=0 ; m < tabAnteri.length ; m++){
												for(var n=0 ; n < tabObject.length ; n++){
													if(tabObject[n].nomTache == tabAnteri[m]){
														var xd = parseInt(tabObject[n].xf);
														var yd = parseInt(tabObject[n].yf);
														var xdFleche = xd+r;
														var ydFleche =  yd;
														var xfFleche = xC-r;
														var yfFleche = yC;
														courbeBezier(xdFleche,ydFleche,xfFleche,yfFleche,'',0,7);
														
													}
												}
											}
										}
									}
									cercle(xC,yC,'fin');
									marquerTache(currentTacheSucc);
									positionFinale(currentTacheSucc,xd,yd,xC,yC);
									numTache = numTache+1;
								}
							}
							j++;
						}
					comptdessiner++;
					if(comptdessiner==tabObjectCompte-1){
						clearInterval(interval);
						comptdessiner = 0;
						interval = setInterval(placerDateTot,1000);
					}
					
				}

				function placerDateTot(){
					$('.titre_action').html('Date au plutôt');
					var succDebut = tabObject[0].suivant;
					var tabSuccDebut = succDebut.split(',');
				
					var i=compteDateTot, tabObjectCompte = tabObject.length, tacheSucc, tabTacheSucc, xC, yC, dureeTacheCourante, currentTacheSucc;

						tacheSucc = tabObject[i].suivant;
						tabTacheSucc = tacheSucc.split(',');
						var tacheSuccCompte = tabTacheSucc.length;
						var nbrTacheAplacer = parseInt(tacheSuccCompte);
						var j=0;
						
						//Date au plutot de la tache courante
						var dateTotAnt = parseInt(tabObject[i].dateTot);
						
						//Placer les dates au plutot de l'item courant de tabObject ==> Monde des taches succésseures
						while(j < tacheSuccCompte){		
							currentTacheSucc = tabTacheSucc[j];
							dureeTacheCourante = parseInt(dureeCurrentTache(currentTacheSucc));
							var dateTot = dateTotAnt+dureeTacheCourante;
							//alert(currentTacheSucc);
							for(var k=0 ; k < tabObject.length ; k++){
								if(tabObject[k].nomTache == currentTacheSucc){
									xC = parseInt(tabObject[k].xf);
									yC = parseInt(tabObject[k].yf);
									
									if(tabObject[k].dateTot == 0){
										tabObject[k].dateTot = dateTot;
										rectangleDateTot(xC,yC,dateTot);
									}
									else if((tabObject[k].dateTot > dateTot) && (tabObject[k].dateTot != 0)){
										dateTot = tabObject[k].dateTot;
										effacerRactangleDateTot(xC,yC);
										rectangleDateTot(xC,yC,dateTot);
									}
									else if((tabObject[k].dateTot <= dateTot) && (tabObject[k].dateTot != 0)){
										tabObject[k].dateTot = dateTot;
										effacerRactangleDateTot(xC,yC);
										rectangleDateTot(xC,yC,dateTot);
									}
								}
							}
							

							j++;
						}
					compteDateTot++;
					if(compteDateTot == tabObjectCompte-1){
						clearInterval(interval);
						compteDateTot =0;
						cheminCritique();
						interval = setInterval(placerDateTard,1000);
					}
				}

				function cheminCritique(){
					$('.titre_action').html('Chemin critique');
					var lastIndex = tabObject.length-1;
					var antFin = tabObject[lastIndex].precedent;
					var tabAnt = antFin.split(',');
					var dateTotFin = parseInt(tabObject[lastIndex].dateTot);
					var dureeTacheFin = parseInt(tabObject[lastIndex].dureeTache);
					var datePreced = dateTotFin- dureeTacheFin;
					var tabAntCompte = tabAnt.length;
					var i=0;
					while((i < tabAntCompte) && (tabAnt[i]!='-')){
						for(var j=0 ; j < tabObject.length ; j++){
							if(tabAnt[i]  == tabObject[j].nomTache){
								if(tabObject[j].dateTot == datePreced){
									
									i=0;
									var xCol = tabObject[j].xf;
									var yCol = tabObject[j].yf;
									//alert('Coloration de la tache'+tabObject[j].nomTache);
									cercleRouge(xCol,yCol);

									var previousTache = tabObject[j].precedent;
									var tabAnt = previousTache.split(',');
									tabAntCompte = tabAnt.length;

									var dateTotTache = parseInt(tabObject[j].dateTot);
									var durationTache = parseInt(tabObject[j].dureeTache);
									datePreced = dateTotTache-durationTache;
								}
								else{
									i++;
								}
							}
						}
					}
					cercleRouge(tabObject[0].xf,tabObject[0].yf);
				}

				var compteDateTard=1;
				function placerDateTard(){
					$('.titre_action').html('Date au plutard');
					var i=compteDateTard;

						var xC, yC, j=0;
						var suc = tabObject[i].suivant;
						var tabSuc = suc.split(',');
						var dateTardTache;

						while(j < tabObject.length){
							if(tabObject[j].nomTache == tabSuc[0]){
								var dureTache = parseInt(tabObject[j].dureeTache);
								var totTache = parseInt(tabObject[j].dateTot);
								dateTardTache = totTache-dureTache;
								break;
							}
							j++;
						}
						tabObject[i].dateTard = dateTardTache;
						xC = parseInt(tabObject[i].xf);
						yC = parseInt(tabObject[i].yf);
						rectangleDateTard(xC,yC,dateTardTache);
						compteDateTard++;
					
					if(compteDateTard == tabObject.length-1){
						clearInterval(interval);
						compteDateTard =1;
						interval = setInterval(margeRetard,1000);
					}
				}
				var compteur=1;
				function margeRetard(){
					$('.titre_action').html('Marge de retard');
					var i=1, tacheCourante;
						var xC, yC;
						var tacheCourante = tabObject[compteur].nomTache;
						//alert('Marge de retard pour la tache '+tacheCourante);
						var dateTardTache, dateTotTache, margeDeRetardTache;

						dateTardTache = parseInt(tabObject[compteur].dateTard);
						dateTotTache = parseInt(tabObject[compteur].dateTot);
						margeDeRetardTache = dateTardTache-dateTotTache;
						
						tabObject[compteur].margeDeRetard = margeDeRetardTache;
						xC = parseInt(tabObject[compteur].xf);
						yC = parseInt(tabObject[compteur].yf);
						rectangleMargeRetard(xC,yC,margeDeRetardTache);
						compteur++;
						//alert(compteur+'  '+TacheObject.length+" "+tabObject[compteur].nomTache);
					if(compteur == tabObject.length-1){
						clearInterval(interval);
						compteur = 1;
						fin();
					}
				}

				function fin(){
					$('#btn_go').hide();
					var to = setTimeout(function(){
						$('#btn_replay').show();
						clearTimeout(to);
					}, 2000);
					$('.titre_action').html('');

					for(var i=0 ; i<tabObject.length ; i++){
						tabObject[i].marque = false;
					}
					numTache=1;
				}
			});

		</script>
	</body>
</html>