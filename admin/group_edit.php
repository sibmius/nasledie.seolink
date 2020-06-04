<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");


$MODULE_ID = 'nasledie.seolink';

use \Bitrix\Main\Localization\Loc as Loc;

\Bitrix\Main\Loader::includeModule($MODULE_ID);

Loc::loadMessages(__FILE__);


$bVarsFromForm = false;




$aTabs = array(
	array("DIV" => "edit", "TAB" => GetMessage("GROUP_TAB"), "ICON" => "main_user_edit", "TITLE" => GetMessage("KEY_URL")),
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
		"ZONE" => "'" . $DB->ForSql($ZONE) . "'",
		"IBLOCK_ID" => "'" . $DB->ForSql($IBLOCK_ID) . "'",
		'FIELD' => "'" . (isset($FIELD) && !empty($FIELD) ? $DB->ForSql(json_encode($FIELD)) : '') . "'",
		'PROPERTY' => "'" . (isset($PROPERTY) && !empty($PROPERTY) ? $DB->ForSql(json_encode( array_unique( $PROPERTY))) : '') . "'",
	);
	// сохранение данных
	$DB->StartTransaction();
	if ($ID > 0) {
		$DB->Update("n_seolink_group", $arFields, "WHERE ID='" . $ID . "'", $err_mess . __LINE__);
	} else {
		$ID = $DB->Insert("n_seolink_group", $arFields, $err_mess . __LINE__);
	}
	$DB->Commit();

	if ($ID > 0) {
		// если сохранение прошло удачно - перенаправим на новую страницу 
		// (в целях защиты от повторной отправки формы нажатием кнопки "Обновить" в браузере)
		if ($apply != "") {
			// если была нажата кнопка "Применить" - отправляем обратно на форму.
			LocalRedirect("/bitrix/admin/nasledie.seolink_group_edit.php?ID=" . $ID . "&mess=ok&lang=" . LANG . "&" . $tabControl->ActiveTabParam());
		} else {
			// если была нажата кнопка "Сохранить" - отправляем к списку элементов.
			LocalRedirect("/bitrix/admin/nasledie.seolink_group.php?lang=" . LANG);
		}
	}else{
		
	}
}
$str_NAME="";
$str_IBLOCK_ID=0;
$str_ZONE='E';
$str_FIELD=array();
$str_PROPERTY=array();

if($ID>0){
	$sql="SELECT * FROM `n_seolink_group` WHERE `ID`='".$DB->ForSql($ID)."' LIMIT 1";
	$rsData = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);
	if($row=$rsData->Fetch()){
		$str_NAME=$row['NAME'];
		$str_ZONE=$row['ZONE'];
		$str_IBLOCK_ID=$row['IBLOCK_ID'];
		if(!empty($row['FIELD'])){
			$str_FIELD= json_decode($row['FIELD'],true);
		}
		if(!empty($row['PROPERTY'])){
			$str_PROPERTY= json_decode($row['PROPERTY'],true);
		}
	}else{
		$ID=0;
	}
}


if ($ID > 0) {
	$APPLICATION->SetTitle(GetMessage("GROUP_TITLE_EDIT"));
} else {
	$APPLICATION->SetTitle(GetMessage("GROUP_TITLE_ADD"));
}



require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
$IB_Type = array();
if (CModule::IncludeModule("iblock")) {
	$db_iblock_type = CIBlockType::GetList();
	while ($ar_iblock_type = $db_iblock_type->Fetch()) {
		if ($arIBType = CIBlockType::GetByIDLang($ar_iblock_type["ID"], LANG)) {
			$IB_Type[$ar_iblock_type['ID']] = array('NAME' => $arIBType['NAME']);
		}
	}

	$res = CIBlock::GetList();
	while ($ar_res = $res->Fetch()) {
		$IB_Type[$ar_res['IBLOCK_TYPE_ID']]['LIST'][$ar_res['ID']] = array('ID' => $ar_res['ID'], 'NAME' => $ar_res['NAME']);
		$properties = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("ACTIVE" => "Y", "IBLOCK_ID" => $ar_res['ID'], 'PROPERTY_TYPE' => 'S', 'USER_TYPE' => 'HTML'));
		while ($prop_fields = $properties->GetNext()) {
			$IB_Type[$ar_res['IBLOCK_TYPE_ID']]['LIST'][$ar_res['ID']]['PROPS'][$prop_fields["ID"]] = array(
				'ID' => $prop_fields["ID"],
				'CODE' => $prop_fields["CODE"],
				'NAME' => $prop_fields["NAME"],
			);
		}
	}
}




if ($_REQUEST["mess"] == "ok" && $ID > 0) {
	CAdminMessage::ShowMessage(array("MESSAGE" => GetMessage("GROUP_OK"), "TYPE" => "OK"));
}

if ($message) {
	echo $message->Show();
} elseif ($error) {
	CAdminMessage::ShowMessage($error);
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
		<td><span class="required">*</span><? echo GetMessage("TBL_IBLOCK_ID") ?></td>
		<td>
			<select name="IBLOCK_ID" size="10" onchange="selectprops(this.value);">
				<?
				foreach ($IB_Type as &$TYPE) {
					if (!empty($TYPE['LIST'])) {
						?>
						<optgroup label="<?= $TYPE['NAME'] ?>">
							<? foreach ($TYPE['LIST'] as &$IBLOCK) { ?>
								<option value="<?= $IBLOCK['ID'] ?>" <? if ($IBLOCK['ID'] == $str_IBLOCK_ID) { ?>selected<? } ?>><?= $IBLOCK['NAME'] ?></option>
							<? } ?>
						</optgroup>
						<?
					}
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><? echo GetMessage("TBL_ZONE") ?></td>
		<td>
			<label>
				<input type="radio" name="ZONE" value="E" <?if($str_ZONE=='E'){?>checked<?}?>><? echo GetMessage("TBL_ZONE_E") ?>
			</label>
			<label>
				<input type="radio" name="ZONE" value="S" <?if($str_ZONE=='S'){?>checked<?}?>><? echo GetMessage("TBL_ZONE_S") ?>
			</label>
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><? echo GetMessage("TBL_FIELD") ?></td>
		<td>
			<select name="FIELD[]" multiple  size="2">
				<option value="PREVIEW_TEXT" <? if (in_array('PREVIEW_TEXT', $str_FIELD)) { ?>selected<? } ?>><?= GetMessage('TBL_PREVIEW_TEXT') ?></option>
				<option value="DETAIL_TEXT" <? if (in_array('DETAIL_TEXT', $str_FIELD)) { ?>selected<? } ?>><?= GetMessage('TBL_DETAIL_TEXT') ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><? echo GetMessage("TBL_PROP") ?></td>
		<td>
			<select name="PROPERTY[]" multiple  size="5" id="propslist">
				<?
				foreach ($IB_Type as &$TYPE) {
					if (!empty($TYPE['LIST'])) {
						foreach ($TYPE['LIST'] as &$IBLOCK) {
							if (!empty($IBLOCK['PROPS'])) {
								?>
								<optgroup label="<?= $IBLOCK['NAME'] ?>" id="IB<?= $IBLOCK['ID'] ?>" <? if ($IBLOCK['ID'] != $str_IBLOCK_ID) { ?>style="display:none;"<? } ?>>
									<? foreach ($IBLOCK['PROPS'] as &$PROP) { ?>
										<option value="<?= $PROP['CODE'] ?>" <? if (in_array($PROP['ID'], $str_PROPERTY) || in_array($PROP['CODE'], $str_PROPERTY)) { ?>selected<? } ?>><?= $PROP['NAME'] ?></option>
									<? } ?>
								</optgroup>
								<?
							}
						}
					}
				}
				?>
			</select>
			<script>
				window.selectprops = selectprops = function (v) {
					sg = document.querySelectorAll('#propslist optgroup');
					for (i = 0; i < sg.length; i++) {
						if (sg[i].id == 'IB' + v) {
							sg[i].style.display = '';
						} else {
							sg[i].style.display = 'none';
						}
					}
				}
			</script>
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