<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");


$MODULE_ID = 'nasledie.seolink';

use \Bitrix\Main\Localization\Loc as Loc;

\Bitrix\Main\Loader::includeModule($MODULE_ID);

Loc::loadMessages(__FILE__);

$sTableID = "tbl_group"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "ID", "desc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

$FilterArr = Array(
	"find_id",
	"find_name",
	"find_iblock",
	"find_field",
	"find_prop",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array(
	"ID" => $find_id,
	"NAME" => $find_name,
	"IBLOCK_ID" => $find_iblock,
	"FIELD" => $find_field,
	"PROPERTY" => $find_prop,
);

global $DB;

$sql = "SELECT * FROM `n_seolink_group`";


if ($set_filter == 'Y') {

	$where = array();
	if ($find != '' && $find_type != '') {
		$where[] = "`" . $find_type . "` = '" . $DB->ForSql($find) . "' ";
	}

	foreach ($arFilter as $F => $V) {
		if ($V != '') {
			$where[] = "`" . $F . "` = '" . $DB->ForSql($V) . "' ";
		}
	}
	if (!empty($where)) {
		$sql .= " WHERE " . implode(' AND ', $where);
	}
}

if ($by != '' && $order != '') {
	$sql .= " ORDER BY " . $by . " " . $order;
}
$rsData = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("URL_TITLE")));



$lAdmin->AddHeaders(array(
	array("id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"default" => true,
	),
	array("id" => "NAME",
		"content" => GetMessage("TBL_NAME"),
		"sort" => "NAME",
		"default" => true,
	),
	array("id" => "ZONE",
		"content" => GetMessage("TBL_ZONE"),
		"sort" => "ZONE",
		"default" => true,
	),
	array("id" => "IBLOCK_ID",
		"content" => GetMessage("TBL_IBLOCK_ID"),
		"sort" => "IBLOCK_ID",
		"default" => true,
	),
	array("id" => "FIELD",
		"content" => GetMessage("TBL_FIELD"),
		//"sort" => "FIELD",
		"default" => true,
	),
	array("id" => "PROPERTY",
		"content" => GetMessage("TBL_PROP"),
		//"sort" => "PROPERTY",
		"default" => true,
	),
));

$arPropsList=array();
if (CModule::IncludeModule("iblock")) {
	$arPropsList = array();
	$properties = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array('PROPERTY_TYPE' => 'S', 'USER_TYPE' => 'HTML'));
	while ($prop_fields = $properties->GetNext()) {
		$arPropsList[$prop_fields["ID"]] = $prop_fields["NAME"];
		$arPropsList[$prop_fields["CODE"]] = $prop_fields["NAME"];
	}
}

while ($arRes = $rsData->NavNext(true, "f_")) {  // создаем строку. результат - экземпляр класса CAdminListRow
	$row = & $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("NAME", '<a href="nasledie.seolink_group_edit.php?ID=' . $f_ID . '&lang=' . LANG . '">' . $f_NAME . '</a>');
	if (!empty($arRes['FIELD'])) {
		$f_FIELD = json_decode($arRes['FIELD'], true);
		if (is_array($f_FIELD)) {
			foreach ($f_FIELD as &$F) {
				$F = GetMessage('TBL_' . $F);
			}
		}
	}
	if ($arRes['PROPERTY'] != '') {
		$f_PROPERTY = json_decode($arRes['PROPERTY'], true);
		if (is_array($f_PROPERTY)) {
			foreach ($f_PROPERTY as &$F) {
				$F = $arPropsList[$F];
			}
		}
	}
	$row->AddViewField("ZONE", GetMessage('TBL_ZONE_'.$f_ZONE));
	$row->AddViewField("IBLOCK_ID", $f_IBLOCK_ID);
	$row->AddViewField("FIELD", implode('<br>', $f_FIELD));
	$row->AddViewField("PROPERTY", implode('<br>', $f_PROPERTY));

	$arActions = Array();

	$arActions[] = array(
		"ICON" => "edit",
		"DEFAULT" => true,
		"TEXT" => GetMessage("GROUP_EDIT"),
		"ACTION" => $lAdmin->ActionRedirect("nasledie.seolink_group_edit.php?ID=" . $f_ID)
	);

	$arActions[] = array(
		"ICON" => "delete",
		"TEXT" => GetMessage("GROUP_DEL"),
		"ACTION" => "if(confirm('" . GetMessage('DEL_CONFIRM') . "')) " . $lAdmin->ActionRedirect("nasledie.seolink_group_del.php?ID=" . $f_ID)
	);

	$row->AddActions($arActions);
}
$lAdmin->AddFooter(
	array(
		array("title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()), // кол-во элементов
		array("counter" => true, "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"), // счетчик выбранных элементов
	)
);

$aContext = array(
	array(
		"TEXT" => GetMessage("POST_ADD"),
		"LINK" => "nasledie.seolink_group_edit.php?lang=" . LANG,
		"TITLE" => GetMessage("POST_ADD_TITLE"),
		"ICON" => "btn_new",
	),
);
$lAdmin->CheckListMode();


$lAdmin->AddAdminContextMenu($aContext);

$APPLICATION->SetTitle(GetMessage("GROUP_TITLE"));


require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");





$oFilter = new CAdminFilter(
	$sTableID . "_filter", array(
	"ID",
	GetMessage("TBL_NAME"),
	GetMessage("TBL_IBLOCK_ID"),
	/* GetMessage("TBL_FIELD"),
	  GetMessage("TBL_PROP"), */
	)
);
?>
<form name="find_form" method="get" action="<? echo $APPLICATION->GetCurPage(); ?>">
	<? $oFilter->Begin(); ?>
	<tr>
		<td><b><?= GetMessage("TBL_FIND") ?>:</b></td>
		<td>
			<input type="text" size="25" name="find" value="<? echo htmlspecialchars($find) ?>">
			<?
			$arr = array(
				"reference" => array(
					"ID",
					GetMessage("TBL_NAME"),
					GetMessage("TBL_IBLOCK_ID"),
				/* GetMessage("TBL_FIELD"),
				  GetMessage("TBL_PROP"), */
				),
				"reference_id" => array(
					"ID",
					"NAME",
					"IBLOCK_ID",
				/* "FIELD",
				  "PROPERTY", */
				)
			);
			echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
			?>
		</td>
	</tr>

	<tr>
		<td><?= "ID" ?>:</td>
		<td>
			<input type="text" name="find_id"  value="<? echo htmlspecialchars($find_id) ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("TBL_NAME") . ":" ?></td>
		<td><input type="text" name="find_name"  value="<? echo htmlspecialchars($find_name) ?>"></td>
	</tr>

	<tr>
		<td><?= GetMessage("TBL_IBLOCK_ID") . ":" ?></td>
		<td><input type="text" name="find_iblock"  value="<? echo htmlspecialchars($find_iblock) ?>"></td>
	</tr>

	<? /* <tr>
	  <td><?= GetMessage("TBL_FIELD") . ":" ?></td>
	  <td><input type="text" name="find_field"  value="<? echo htmlspecialchars($find_field) ?>"></td>
	  </tr>

	  <tr>
	  <td><?= GetMessage("TBL_PROP") . ":" ?></td>
	  <td><input type="text" name="find_prop"  value="<? echo htmlspecialchars($find_prop) ?>"></td>
	  </tr>
	 */ ?>
	<?
	$oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPage(), "form" => "find_form"));
	$oFilter->End();
	?>
</form>

<?
// выведем таблицу списка элементов
$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>