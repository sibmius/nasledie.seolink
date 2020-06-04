<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");


$MODULE_ID = 'nasledie.seolink';

use \Bitrix\Main\Localization\Loc as Loc;

\Bitrix\Main\Loader::includeModule($MODULE_ID);

Loc::loadMessages(__FILE__);


$bVarsFromForm = false;




$aTabs = array(
	array("DIV" => "edit", "TAB" => GetMessage("KEY_URL"), "ICON" => "main_user_edit", "TITLE" => GetMessage("KEY_URL")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);


$ID = intval($ID);
$message = null;  // сообщение об ошибке
$bVarsFromForm = false;

if (
	$REQUEST_METHOD == "POST" // проверка метода вызова страницы
	&&
	($save != "" || $apply != "") // проверка нажатия кнопок "Сохранить" и "Применить"
	&&
	check_bitrix_sessid()  // проверка идентификатора сессии
) {
	global $DB;




	// обработка данных формы
	$arFields = array(
		"NAME" => "'" . $DB->ForSql($NAME) . "'",
		"URL" => "'" . $DB->ForSql($URL) . "'",
	);

	$parsed_url = parse_url($URL);
	$scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : 'https://';
	$host = isset($parsed_url['host']) ? $parsed_url['host'] : $_SERVER['SERVER_NAME'];
	$port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
	$user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
	$pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
	$pass = ($user || $pass) ? "$pass@" : '';
	$path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
	$query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
	$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

	$arFields['GLOBAL_URL'] ="'" . $DB->ForSql($scheme . $user . $pass . $host . $port . $path . $query . $fragment). "'";
	$arFields['LOCAL_URL'] = "'" .$DB->ForSql($path . $query . $fragment). "'";

	unset($parsed_url, $scheme, $user, $pass, $host, $port, $path, $query, $fragment);
	// сохранение данных
	$DB->StartTransaction();
	if ($ID > 0) {
		$DB->Update("n_seolink_url", $arFields, "WHERE ID='" . $ID . "'", $err_mess . __LINE__);
	} else {
		$ID = $DB->Insert("n_seolink_url", $arFields, $err_mess . __LINE__);
	}
	$DB->Commit();
	if ($ID > 0 && is_array($TEXT)) {
		foreach ($TEXT as $n => &$T) {
			if (isset($KEY_ID[$n]) && $KEY_ID[$n] > 0) {
				if (trim($T) != '') {
					$arFields = array('TEXT' => "'" . $DB->ForSql($T) . "'");
					$DB->Update("n_seolink_request", $arFields, "WHERE ID='" . $DB->ForSql($KEY_ID[$n]) . "'", $err_mess . __LINE__);
				} else {
					$DB->Query("DELETE FROM `n_seolink_request` WHERE `ID`='" . $DB->ForSql($KEY_ID[$n]) . "'", false, $err_mess . __LINE__);
				}
			} else {
				if (trim($T) != '') {
					$arFields = array(
						'TEXT' => "'" . $DB->ForSql($T) . "'",
						'URL' => "'" . $DB->ForSql($ID) . "'",
					);
					$DB->Insert("n_seolink_request", $arFields, $err_mess . __LINE__);
				}
			}
		}
	}

	if ($ID > 0) {
		// если сохранение прошло удачно - перенаправим на новую страницу 
		// (в целях защиты от повторной отправки формы нажатием кнопки "Обновить" в браузере)
		if ($apply != "") {
			// если была нажата кнопка "Применить" - отправляем обратно на форму.
			LocalRedirect("/bitrix/admin/nasledie.seolink_url_edit.php?ID=" . $ID . "&mess=ok&lang=" . LANG . "&" . $tabControl->ActiveTabParam());
		} else {
			// если была нажата кнопка "Сохранить" - отправляем к списку элементов.
			LocalRedirect("/bitrix/admin/nasledie.seolink_url.php?lang=" . LANG);
		}
	} else {
		// если в процессе сохранения возникли ошибки - получаем текст ошибки и меняем вышеопределённые переменные
	}
}

$str_NAME = "";
$str_URL = "";
$str_MORE_KEY = array();

if ($ID > 0) {
	$sql = "SELECT * FROM `n_seolink_url` WHERE `ID`='" . $DB->ForSql($ID) . "' LIMIT 1";
	$rsData = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);
	if ($row = $rsData->Fetch()) {
		$str_NAME = $row['NAME'];
		$str_URL = $row['URL'];
		$sql = "SELECT * FROM `n_seolink_request` WHERE `URL`='" . $DB->ForSql($ID) . "'";
		$rsData = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);
		while ($row = $rsData->Fetch()) {
			$str_MORE_KEY[] = $row;
		}
	} else {
		$ID = 0;
	}
}











if ($ID > 0) {
	$APPLICATION->SetTitle(GetMessage("URL_TITLE_EDIT"));
} else {
	$APPLICATION->SetTitle(GetMessage("URL_TITLE_ADD"));
}



require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");


if ($_REQUEST["mess"] == "ok" && $ID > 0) {
	CAdminMessage::ShowMessage(array("MESSAGE" => GetMessage("URL_OK"), "TYPE" => "OK"));
}

if ($message) {
	echo $message->Show();
} elseif ($error) {
	CAdminMessage::ShowMessage($$error);
}
?>

<form method="POST" Action="<? echo $APPLICATION->GetCurPage() ?>" ENCTYPE="multipart/form-data" name="post_form">
	<? echo bitrix_sessid_post(); ?>
	<input type="hidden" name="lang" value="<?= LANG ?>">
	<? if ($ID > 0 && !$bCopy): ?>
		<input type="hidden" name="ID" value="<?= $ID ?>">
	<? endif; ?>
	<?
// отобразим заголовки закладок
	$tabControl->Begin();

	$tabControl->BeginNextTab();
	?>
	<tr>
		<td><span class="required">*</span><? echo GetMessage("TBL_NAME") ?></td>
		<td><input type="text" name="NAME" value="<? echo $str_NAME; ?>" size="30" maxlength="100"></td>
	</tr>
	<tr>
		<td><span class="required">*</span><? echo GetMessage("TBL_URL") ?></td>
		<td><input type="text" name="URL" value="<? echo $str_URL; ?>" size="30" maxlength="100"></td>
	</tr>
	<tr>
		<td><span class="required">*</span><? echo GetMessage("TBL_MORE_KEY") ?></td>
		<td>
			<? foreach ($str_MORE_KEY as $n => &$key) { ?>
				<input type="hidden" name="KEY_ID[<?= $n ?>]" value="<?= $key['ID']; ?>">
				<input type="text" name="TEXT[<?= $n ?>]" value="<?= $key['TEXT']; ?>" size="30" maxlength="100"><br>
			<? } ?>
			<input type="text" name="TEXT[n0]" value="" size="30" maxlength="100"><br>
			<input type="text" name="TEXT[n1]" value="" size="30" maxlength="100"><br>
			<input type="text" name="TEXT[n2]" value="" size="30" maxlength="100"><br>
			<input type="text" name="TEXT[n3]" value="" size="30" maxlength="100"><br>
			<input type="text" name="TEXT[n4]" value="" size="30" maxlength="100"><br>
			<input type="text" name="TEXT[n5]" value="" size="30" maxlength="100"><br>
			<input type="text" name="TEXT[n6]" value="" size="30" maxlength="100"><br>
			<input type="text" name="TEXT[n7]" value="" size="30" maxlength="100"><br>
			<input type="text" name="TEXT[n8]" value="" size="30" maxlength="100"><br>
			<input type="text" name="TEXT[n9]" value="" size="30" maxlength="100"><br>
		</td>
	</tr>
	<?
	$tabControl->Buttons(
		array(
			"back_url" => "nasledie.seolink_url.php?lang=" . LANG,
		)
	);
	$tabControl->End();
	?>
</form>
<? echo BeginNote(); ?>
<span class="required">*</span><? echo GetMessage("REQUIRED_FIELDS") ?>
<? echo EndNote(); ?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>