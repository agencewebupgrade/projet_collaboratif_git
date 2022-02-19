<?php if( session_id()=='' ){ session_start(); }
header('Content-type:text/html; charset=UTF-8');	// encodage UTF-8
// ---------------------
// GUESTBOOK
	$livredortxt	= 'livredor.txt'; // mettre le chemin relatif au fichier txt
	$delimit 	= '-*-'; // délimiteur
// ---------------------
// si le fichier n'existe pas, on le crée.
	if(!file_exists($livredortxt)){
		$fp = fopen($livredortxt,'w+'); // Le "w+" créer le fichier si il n'existe pas
	}
// ---------------------
// IP du visiteur
	$ipvisiteur = $_SERVER["REMOTE_ADDR"];
// ---------------------
// Format d'affichage de la date (au choix)
	//$date		= date ( "d/m/Y H:i:s" ); // avec les heures:minutes:secondes
	$date		= date ( "d/m/Y à H:i" );
	//$date		= date ( "d/m/Y" );
// ---------------------
// Initialisation
	$nom		= '';
	$message	= '';
	$mail		= '';
	$validForm	= true;
	$MsgErreur	= '';
// ---------------------
// TRAITEMENT SI formulaire soumis
if(isset($_POST['LivredorSubmit'], $_POST['antiF5'], $_SESSION['antiF5']) && $_POST['antiF5']==$_SESSION['antiF5'])
{
	// ---------------------
	// RECUPERATION des DONNEES
	//On convertit les caracteres html
	$nom 		= (isset($_POST['nom']))? htmlspecialchars(trim($_POST['nom'])) : '';
	$mail 		= (isset($_POST['mail']))? htmlspecialchars(trim($_POST['mail'])) : '';
	// ---------
	// textarea : attention aux injections de code html !
	$allowable_tags = '<a><b><em><ul><li>'; // (facultatif) on autorise ces balises (voir : http://php.net/manual/fr/function.strip-tags.php )
	$message 	= (isset($_POST['message']))? htmlspecialchars(trim(strip_tags($_POST['message'], $allowable_tags))) : ''; // textarea
	$message 	= nl2br($message); // nl2br() : change les sauts de ligne tapés par le visiteur en <br />
	// ---------
	// on enlève les sauts de ligne résiduels, pour l'écriture dans le fichier (sur une seule ligne)
	$message 	= preg_replace("/(\r\n|\n|\r)/", " ", $message);
	// ---------------------
	// 2EME VERIFICATION : en PHP
	// GESTION d'ERREURS
	// -----------------------
	// CENSURE
	function censorWords($text){
		/*liste des mots a filtrer ou expression aussi longue que tu veux*/
		$find = array(
		'/caca\s/i',
		'/pipi\s/i',
		'/prout\s/i',
		'/censuré\s/i',
		'/censuré\s/i',
		'/censuré\s/i',
		'/censuré\s/i',
		);
		$replace = ' **** ';
		return preg_replace($find,$replace,$text);
	}
	// -----------------------
	// On censure ? (FACULTATIF)
	$newnom 	= censorWords($nom);
	$newmessage = censorWords($message);
	$newmail 	= censorWords($mail);
	$champ_censure = array();
	if ($nom!=$newnom) {			$champ_censure[] = 'Nom'; }
	if ($message!=$newmessage) {	$champ_censure[] = 'Message'; }
	if ($mail!=$newmail) {			$champ_censure[] = 'Email'; }
	if(count($champ_censure)>0) {
		$MsgErreur 	.= 'Ces champs ont été censurés : '.implode(', ',$champ_censure).'<br />';
	}
	// -----------------------
	// champs obligatoires
	$champ_obligatoire = array();
	if ($nom=='' || $newnom=='') {			$validForm = false;		$champ_obligatoire[] = 'Nom'; }
	if ($message=='' || $newmessage=='') {	$validForm = false;		$champ_obligatoire[] = 'Message'; }
	if(count($champ_obligatoire)>0) {
		$MsgErreur 	.= 'Remplissez tous les champs obligatoires : '.implode(', ',$champ_obligatoire).'<br />';
	}
	// -----------------------
	// Vérification du format de l'Email
	if($mail!='' && !filter_var($mail, FILTER_VALIDATE_EMAIL)){
		$validForm 	= false;
		$MsgErreur 	.= 'Invalide Email !<br />';
	}
	// -----------------------
	// OK SI PAS D'ERREUR
	if($validForm === true)
	{
		// ---------------------
		if($newnom!='' && $newmessage!='')
		{
			// ECRITURE dans le GESTBOOK
			// ---------------------
			//Ouverture du fichier en écriture
			$fp 	= fopen($livredortxt,'a'); // 'a' : à la fin du fichier
			$line 	= $newnom.$delimit.$newmessage.$delimit.$newmail.$delimit.$date.$delimit.$ipvisiteur."\n";
			//On rajoute le message
			fwrite($fp, $line, strlen($line));
			//fermeture du fichier
			fclose($fp);
			// ---------------------
		}
		// ---------------------
		// On vide
		$nom 		= '';
		$message 	= '';
		$mail 		= '';
	}
}
// ---------------------
unset($_POST);
// anti-F5 (évite de re-poster le formulaire en cas de F5 ("Actualiser la page")
$_SESSION['antiF5'] = rand(100000,999999);
// ------------------------------------------
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Livre d'Or</title>
 
<style type="text/css">
	div.grande {
		width:70%; 
		margin:20px auto; 
		text-align:left;
		border:3px solid #000000; 
		padding:1px;
	}
	div.grande h1, div.grande h2 { 
		margin:20px auto; 
		text-align:center; 
	}
	/* Formulaire */
	form#livredorform { margin:0 auto; }
	form#livredorform label { display:inline-block; min-width:25%; text-align:right; margin-right:10px; }
	form#livredorform p { line-height:25px; }
	form#livredorform p.errChamps { color:red; }
	/* Affichage */
	.livredor-nom { float:left; }
	.livredor-date {float:right }
	.livredor-message { margin:5px 30px;clear:both; }
</style>
<script type="text/javascript">
// -------------------------------
// 1ERE VERIFICATION : en JavaScript
function validLivredor(){
	var error1 = '';
	var error2 = '';
	var setfocus = 0;
	var idnom = document.getElementById('idnom');	// obligatoire
	var idmessage = document.getElementById('idmessage');	// obligatoire
	var idmail = document.getElementById('idmail');	// (facultatif, mais nécessite une vérification)
 
	// Vérification des champs obligatoires
	if(idnom.value == '' || idnom.value.length < 2) {
		error1 += '- Nom ou Pseudo\n';
		if(setfocus == 0) { idnom.focus();  }
		setfocus += 1;
	}
	if(idmessage.value=='') {
		error1 += '- Message\n';
		if(setfocus == 0) { idmessage.focus();  }
		setfocus += 1;
	}
	// Vérification de l'email
	if(idmail.value!='' && !isEmail( idmail.value )) {
		error2 += '- Email invalide\n';
		if(setfocus == 0) { idmail.focus();  }
		setfocus += 1;
	}
	// erreur ?
	if(error1!='' || error2!='') {
		var msgerror = '';
		if(error1!='') {
			if(setfocus==1) { msgerror += 'Merci de remplir le champ obligatoire :\n'+error1; }
			else { msgerror += 'Merci de remplir les champs obligatoires :\n'+error1; }
		}
		if(error2!='') {
			msgerror += '\nErreur :\n'+error2;
		}
		alert(msgerror);
		return false;
	}
	else {
		document.submit();
	}
};
// -------------------------------
// fonction de vérification EMAIL
function isEmail( Email )
{
	var reg_mail 	= /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]{2,}[.][a-zA-Z]{2,5}$/
	if( reg_mail.exec(Email)!=null )
	{ return true; } else { return false; }
};
// -------------------------------
</script>
</head>
<body>
 
<div class="grande">
	<h1>Livre d'Or</h1>
 
	<form id="livredorform" method="post" action="livredor.php" onsubmit="validLivredor(); return false;">
			<input type="hidden" name="antiF5" value="<?php echo $_SESSION['antiF5']; ?>" />
		<p>
			<label for="idnom">Nom/Pseudo* :</label>
			<input id="idnom" type="text" name="nom" value="<?php if(!$validForm) echo $nom; ?>" size="25" />
		</p>
		<p>
			<label for="idmessage">Message* :</label>
			<textarea id="idmessage" name="message" rows="5" cols="47"><?php if(!$validForm) echo $message; ?></textarea>
		</p>
		<p>
			<label for="idmail">Mail :</label>
			<input id="idmail" type="text" name="mail" value="<?php if(!$validForm) echo $mail; ?>" size="25" /> <i>(facultatif)</i>
		</p>
		<p>
			<label>&nbsp;</label>
			<input type="submit" name="LivredorSubmit" value="Envoyer" />
		</p>
	<?php if(!empty($MsgErreur)) { // erreur ? ?>
		<p class="errChamps"><label>&nbsp;</label><?php echo $MsgErreur; ?></p>
	<?php } ?>
	</form>
 
	<h2>Vos impressions, commentaires:</h2>
<?php 
// ---------------------
// Affichage des commentaires du livre d'Or
// ---------------------
// LECTURE DU FICHIER TEXTE
$lines = file($livredortxt);
// FACULTATIF : reverse pour ordre ANTI-CHRONOLOGIQUE
$lines = array_reverse($lines);
// lecture ligne par ligne
foreach($lines as $line) {
	$line	= trim($line);
	if(strlen($line)>0){
		$vars	= explode($delimit,$line);
		$nom 	= $vars[0];
		$message = html_entity_decode($vars[1]);
		$mail 	= $vars[2];
		$date 	= $vars[3];
		$ip 	= $vars[4];
		$aff = '<p><span class="livredor-nom">De <b>'.$nom.'</b>';
		if($mail!='') { $aff .= ' <i>('.$mail.')</i>'; }
		$aff .= '</span><span class="livredor-date">';
		// Affichage de l'IP UNIQUEMENT pour le visiteur
		if($ipvisiteur == $ip) 
		$aff .= '<i>le '.$date.'</i>';
		$aff .= '</span></p>';
		$aff .= '<p class="livredor-message">'.$message.'</p><hr/>';
		echo $aff;
	}
}
// ---------------------
?>
 
</div>
 
</body>
</html>