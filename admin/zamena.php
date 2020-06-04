<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

if (!function_exists("mb_str_replace")) 
{
    function mb_str_replace($needle, $replace_text, $haystack) {PR(explode($needle, $haystack));
		if(stripos($haystack, $needle)!==false){
			$newstr=mb_substr($haystack, 0, stripos($haystack, $needle)).$replace_text.mb_substr($haystack, stripos($haystack, $needle)+mb_strlen($needle));
			return $newstr;
		}
        return implode($replace_text, mb_split($needle, $haystack));
    }
}
$MODULE_ID = 'nasledie.seolink';

use \Bitrix\Main\Localization\Loc as Loc;

\Bitrix\Main\Loader::includeModule($MODULE_ID);

Loc::loadMessages(__FILE__);

$sTableID = "tbl_zamena"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "ID", "desc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка


$FilterArr = Array(
	"find_id",
	"find_key",
	"find_zone",
	"find_object",
	"find_field",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array(
	"ID" => $find_id,
	"KEY" => $find_key,
	"ZONE" => $find_zone,
	"OBJECT" => $find_object,
	"FIELD" => $find_field,
);

global $DB;


if (($arID = $lAdmin->GroupAction())) {
	foreach ($arID as $ID) {
		if (strlen($ID) <= 0) {
			continue;
		}
		$ID = IntVal($ID);

		// для каждого элемента совершим требуемое действие
		if ($_REQUEST['action'] == 'run') {
			$sql = "SELECT * FROM `n_seolink_zamena` WHERE `ID`='" . $ID . "' LIMIT 1;";
			$res = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);
			if ($row = $res->Fetch()) {
				$sql = "SELECT * FROM `n_seolink_request` WHERE `ID`='" . $row['KEY'] . "' LIMIT 1;";
				$res = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);
				if ($key = $res->Fetch()) {
					$sql = "SELECT * FROM `n_seolink_url` WHERE `ID`='" . $key['URL'] . "' LIMIT 1;";
					$res = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);
					if ($url = $res->Fetch()) {
						$str = '<a href="' . $url['GLOBAL_URL'] . '" title="' . $key['TEXT'] . '">' . $key['TEXT'] . '</a>';
						if (CModule::IncludeModule("iblock")) {
							if ($row['ZONE'] == 'E') {
								$res = CIBlockElement::GetByID($row['OBJECT']);
								if ($ob = $res->GetNextElement()) {
									$arF = $ob->GetFields();
									if ($row['FIELD'] == 'PREVIEW_TEXT') {
										$arNF=array('PREVIEW_TEXT' => mb_str_replace($key['TEXT'], $str, $arF['PREVIEW_TEXT']));
										$el = new CIBlockElement;
										$res = $el->Update($row['OBJECT'], $arNF);
										unset($res, $el);
									} elseif ($row['FIELD'] == 'DETAIL_TEXT') {
										$arNF=array('DETAIL_TEXT' => mb_str_replace($key['TEXT'], $str, $arF['DETAIL_TEXT']));
										$el = new CIBlockElement;
										$res = $el->Update($row['OBJECT'], $arNF);
										unset($res, $el);
									} else {
										$arP = $ob->GetProperty($row['FIELD']);
										if ($arP['MULTIPLE'] == 'Y') {
											foreach ($arP['VALUE'] as &$v) {
												$v['TEXT'] = mb_str_replace($key['TEXT'], $str, $v['TEXT']);
											}
										} else {
											$arP['VALUE']['TEXT'] = mb_str_replace($key['TEXT'], $str, $arP['VALUE']['TEXT']);
										}
										CIBlockElement::SetPropertyValues($row['OBJECT'], $arF['IBLOCK_ID'], $arP['VALUE'], $row['FIELD']);
									}
								}
							} elseif ($row['ZONE'] == 'S') {
								$res = CIBlockSection::GetByID($row['OBJECT']);
								if ($ob = $res->GetNext()) {
									$ob['DESCRIPTION'] = str_replace($key['TEXT'], $str, $ob['DESCRIPTION']);
									$bs = new CIBlockSection;
									$bs->Update($row['OBJECT'], array('DESCRIPTION'=>$ob['DESCRIPTION']));
									unset($bs,$res);
								}
							}
						}
					}
				}
			}
			\Nasledie\SeoLink\NAgent::Calc();
		}
	}
}














$sql = "SELECT * FROM `n_seolink_zamena`";


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
	$sql .= " ORDER BY `" . $by . "` " . $order;
}
$rsData = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("ZAMENA_TITLE")));


$lAdmin->AddHeaders(array(
	array("id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"default" => true,
	),
	array("id" => "KEY",
		"content" => GetMessage("TBL_KEY"),
		"sort" => "KEY",
		"default" => true,
	),
	array("id" => "URL",
		"content" => GetMessage("TBL_URL"),
		//"sort" => "URL",
		"default" => true,
	),
	array("id" => "ZONE",
		"content" => GetMessage("TBL_ZONE"),
		"sort" => "ZONE",
		"default" => true,
	),
	array("id" => "OBJECT",
		"content" => GetMessage("TBL_OBJECT"),
		"sort" => "OBJECT",
		"default" => true,
	),
	array("id" => "FIELD",
		"content" => GetMessage("TBL_FIELD"),
		"sort" => "FIELD",
		"default" => true,
	),
));



$arPropsList = array();
if (CModule::IncludeModule("iblock")) {
	$arPropsList = array();
	$properties = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array('PROPERTY_TYPE' => 'S', 'USER_TYPE' => 'HTML'));
	while ($prop_fields = $properties->GetNext()) {
		$arPropsList[$prop_fields["ID"]] = $prop_fields["NAME"];
		$arPropsList[$prop_fields["CODE"]] = $prop_fields["NAME"];
	}
}

$arPropsList['DESCRIPTION'] = GetMessage('F_DESCRIPTION');
$arPropsList['PREVIEW_TEXT'] = GetMessage('F_PREVIEW_TEXT');
$arPropsList['DETAIL_TEXT'] = GetMessage('F_DETAIL_TEXT');


while ($arRes = $rsData->NavNext(true, "f_")) {  // создаем строку. результат - экземпляр класса CAdminListRow
	$row = & $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("ID", $f_ID);

	$sql = "SELECT * FROM `n_seolink_request` WHERE `ID`='" . $f_KEY . "' LIMIT 1;";

	$res = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);
	if ($res = $res->Fetch()) {
		$row->AddViewField("KEY", '<a href="nasledie.seolink_url_edit.php?ID=' . $res['URL'] . '&lang=' . LANG . '">' . $res['TEXT'] . '</a>');
		$sql = "SELECT * FROM `n_seolink_url` WHERE `ID`='" . $res['URL'] . "' LIMIT 1;";

		$res = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);
		if ($res = $res->Fetch()) {
			$row->AddViewField("URL", '<a href="nasledie.seolink_url_edit.php?ID=' . $res['ID'] . '&lang=' . LANG . '">' . $res['URL'] . '</a>');
		}
	}
	unset($res);




	$row->AddViewField("ZONE", GetMessage('TBL_ZONE_' . $f_ZONE));
	if ($f_ZONE == 'E') {
		$res = CIBlockElement::GetByID($f_OBJECT);
		if ($res = $res->GetNextElement()) {
			$res=$res->GetFields();
			if(stripos($res['DETAIL_PAGE_URL'],'#YEAR#')!=false){
				$res['DETAIL_PAGE_URL']=str_replace('#YEAR#', '0', $res['DETAIL_PAGE_URL']);
			}
			$row->AddViewField("OBJECT", '<a href="'.$res['DETAIL_PAGE_URL'].'">['.$f_OBJECT.'] '.$res['NAME'].'</a>');
		}
	} elseif ($f_ZONE == 'S') {
		$res = CIBlockSection::GetByID($f_OBJECT);
		if ($res = $res->GetNext()) {
			$row->AddViewField("OBJECT", '<a href="'.$res['SECTION_PAGE_URL'].'">['.$f_OBJECT.'] '.$res['NAME'].'</a>');
		}
	}
	
	unset($res);

	$row->AddViewField("FIELD", $arPropsList[$f_FIELD]);
}
$lAdmin->AddFooter(
	array(
		array("title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()), // кол-во элементов
		array("counter" => true, "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"), // счетчик выбранных элементов
	)
);

$lAdmin->AddGroupActionTable(Array(
	"run" => GetMessage("ADDLINK"),
));


$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("ZAMENA_TITLE"));


require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");



$oFilter = new CAdminFilter(
	$sTableID . "_filter", array(
	"ID",
	GetMessage("TBL_KEY"),
	GetMessage("TBL_ZONE"),
	GetMessage("TBL_OBJECT"),
	GetMessage("TBL_FIELD"),
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
					GetMessage("TBL_KEY"),
					GetMessage("TBL_ZONE"),
					GetMessage("TBL_OBJECT"),
					GetMessage("TBL_FIELD"),
				),
				"reference_id" => array(
					"ID",
					"KEY",
					"ZONE",
					"OBJECT",
					"FIELD",
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
		<td><?= GetMessage("TBL_KEY") . ":" ?></td>
		<td><input type="text" name="find_key"  value="<? echo htmlspecialchars($find_key) ?>"></td>
	</tr>

	<tr>
		<td><?= GetMessage("TBL_ZONE") . ":" ?></td>
		<td>
			<?
			$arr = array(
				"reference" => array(
					GetMessage("TBL_ZONE_E"),
					GetMessage("TBL_ZONE_S"),
				),
				"reference_id" => array(
					"E",
					"S",
				)
			);
			echo SelectBoxFromArray("find_zone", $arr, $find_zone, "", "");
			?>
	</tr>

	<tr>
		<td><?= GetMessage("TBL_OBJECT") . ":" ?></td>
		<td><input type="text" name="find_object"  value="<? echo htmlspecialchars($find_object) ?>"></td>
	</tr>

	<tr>
		<td><?= GetMessage("TBL_FIELD") . ":" ?></td>
		<td><input type="text" name="find_field"  value="<? echo htmlspecialchars($find_field) ?>"></td>
	</tr>

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