<script type="text/javascript">
	function placerDateTard(){
					
				}
				function cheminCritique(){
					var tabObjectCount = (tabObject.length)-1;
					var tacheAnt = tabObject[tabObjectCount].precedent;
					var datePlutot = parseInt(tabObject[tabObjectCount].dateTot);
					var dureeCurrentTache = parseInt(tabObject[tabObjectCount].dureeTache);
					var dateTotPreced = datePlutot-dureeCurrentTache;
					var tabTacheAnt = new Array();
					tabTacheAnt = tacheAnt.split(',');


					var compteur = tabTacheAnt.length;
					var i=0
					while(i < compteur){
						for(var j=0 ; j < tabObject.length ; j++){
							if(tabObject[j].nomTache == tabTacheAnt[i]){
								if(tabObject[j].dateTot == dateTotPreced){
									var xC,yC;
									xC = parseInt(tabObject[j].xf);
									yC = parseInt(tabObject[j].yf);
									cercleRouge(xC,yC);
									alert('Colorer la tache '+tabObject[j].nomTache+' en rouge');
									var previousTache = tabObject[j].precedent;
									var tabTacheAnt = previousTache.split(',');
									datePlutot = parseInt(tabObject[j].dateTot);
									dureeCurrentTache = parseInt(tabObject[j].dureeTache);
									dateTotPreced = datePlutot-dureeCurrentTache;
									compteur = tabTacheAnt.length;
									i=0;
								}
								else{
									i++;
								}
							}
						}
					}


					
				}
</script>
