<?

namespace Nasledie\SeoLink;

global $DB, $APPLICATION, $MESS, $DBType;

$MODULE_ID = 'nasledie.seolink';


/*
  \Bitrix\Main\Loader::registerAutoLoadClasses(
  $MODULE_ID,
  array(
  'NSeo' => 'classes/general/seo_utils.php',
  'CSeoKeywords' => 'classes/general/seo_keywords.php',
  'CSeoPageChecker' => 'classes/general/seo_page_checker.php'
  )
  );
 */

class NAgent {

	public static $GROUP = array();
	public static $URL = array();
	public static $KEY = array();
	public static $STATS = array();
	public static $ZAMENA = array();

	public static function Calc() {
		if (\CModule::IncludeModule("iblock")) {
			global $DB;
			$sql = "SELECT * FROM `n_seolink_url`;";
			$res = $DB->Query($sql, false, $err_mess . __LINE__);
			while ($row = $res->Fetch()) {
				if ($row['GLOBAL_URL'] != '') {
					$row['GLOBAL_URL'] = '"' . $row['GLOBAL_URL'] . '"';
				}
				if ($row['LOCAL_URL'] != '') {
					$row['LOCAL_URL'] = '"' . $row['LOCAL_URL'] . '"';
				}
				self::$URL[$row['ID']] = $row;
			}
			$sql = "SELECT * FROM `n_seolink_request`;";
			$res = $DB->Query($sql, false, $err_mess . __LINE__);
			while ($row = $res->Fetch()) {
				self::$KEY[$row['ID']] = $row;
			}
			$sql = "SELECT * FROM `n_seolink_group`;";
			$res = $DB->Query($sql, false, $err_mess . __LINE__);
			while ($row = $res->Fetch()) {
				if ($row['IBLOCK_ID'] > 0) {
					if ($row['FIELD'] != '') {
						$row['FIELD'] = json_decode($row['FIELD'], true);
					}
					if ($row['PROPERTY'] != '') {
						$row['PROPERTY'] = json_decode($row['PROPERTY'], true);
					}
					self::$GROUP[$row['ID']] = $row;
				}
			}

			foreach (self::$GROUP as &$group) {
				if ((!empty($group['FIELD']) || !empty($group['PROPERTY'])) && $group['ZONE'] == 'E') {
					$arFilter = array('IBLOCK_ID' => $group['IBLOCK_ID']);
					$res = \CIBlockElement::GetList(Array(), $arFilter);
					while ($ob = $res->GetNextElement()) {
						$arFields = $ob->GetFields();
						if (!empty($group['FIELD'])) {
							foreach (self::$KEY as &$u) {
								foreach ($group['FIELD'] as &$F) {
									if (stripos($arFields[$F], $u['TEXT']) !== false) {
										self::$STATS['KEY'][$u['ID']][$group['ID']][$arFields['ID']] = true;
										if (self::$URL[$u['URL']]['URL'] != '') {
											$lurl = self::$URL[$u['URL']];
											if (stripos($arFields[$F], $lurl['GLOBAL_URL']) === false && stripos($arFields[$F], $lurl['LOCAL_URL']) === false) {
												self::$ZAMENA[$u['ID']]['E'][$arFields['ID']][] = $F;
											} else {
												self::$STATS['URLKEY'][$u['ID']][$group['ID']][$arFields['ID']] = true;
											}
										}
									}
								}
								unset($F);
							}
							unset($u);
							foreach (self::$URL as &$u) {
								if ($u['URL'] != '') {
									foreach ($group['FIELD'] as &$F) {
										if (stripos($arFields[$F], $u['GLOBAL_URL']) !== false || stripos($arFields[$F], $u['LOCAL_URL']) !== false) {
											self::$STATS['URL'][$u['ID']][$group['ID']][$arFields['ID']] = true;
										}
									}
								}
								unset($F);
							}
							unset($u);
						}
						if (!empty($group['PROPERTY'])) {
							$arProperties = $ob->GetProperties();
							foreach (self::$KEY as &$u) {
								foreach ($group['PROPERTY'] as &$F) {
									if (!isset($arProperties[$F]['VALUE']['TEXT']) && is_array($arProperties[$F]['VALUE'])) {
										foreach ($arProperties[$F]['VALUE'] as &$text) {
											$text = $text['TEXT'];
										}
										$arProperties[$F]['VALUE'] = implode('', $arProperties[$F]['VALUE']);
									} elseif (isset($arProperties[$F]['VALUE']['TEXT'])) {
										$arProperties[$F]['VALUE'] = $arProperties[$F]['VALUE']['TEXT'];
									}
									if (stripos($arProperties[$F]['VALUE'], $u['TEXT']) !== false) {
										self::$STATS['KEY'][$u['ID']][$group['ID']][$arFields['ID']] = true;
										if (self::$URL[$u['URL']]['URL'] != '') {
											$lurl = self::$URL[$u['URL']];
											if (stripos($arFields[$F], $lurl['GLOBAL_URL']) === false && stripos($arFields[$F], $lurl['LOCAL_URL']) === false) {
												self::$ZAMENA[$u['ID']]['E'][$arFields['ID']][] = $F;
											} else {
												self::$STATS['URLKEY'][$u['ID']][$group['ID']][$arFields['ID']] = true;
											}
										}
									}
								}
								unset($F);
							}
							unset($u);
							foreach (self::$URL as &$u) {
								if ($u['URL'] != '') {
									foreach ($group['PROPERTY'] as &$F) {
										if (!isset($arProperties[$F]['VALUE']['TEXT']) && is_array($arProperties[$F]['VALUE'])) {
											foreach ($arProperties[$F]['VALUE'] as &$text) {
												$text = $text['TEXT'];
											}
											$arProperties[$F]['VALUE'] = implode('', $arProperties[$F]['VALUE']);
										} elseif (isset($arProperties[$F]['VALUE']['TEXT'])) {
											$arProperties[$F]['VALUE'] = $arProperties[$F]['VALUE']['TEXT'];
										}
										if (stripos($arProperties[$F]['VALUE'], $u['GLOBAL_URL']) !== false || stripos($arProperties[$F]['VALUE'], $u['LOCAL_URL']) !== false) {
											self::$STATS['URL'][$u['ID']][$group['ID']][$arFields['ID']] = true;
										}
									}
								}
								unset($F);
							}
							unset($u);
							unset($arProperties);
						}
					}
				} elseif ($group['ZONE'] == 'S') {
					$arFilter = array('IBLOCK_ID' => $group['IBLOCK_ID']);
					$res = \CIBlockSection::GetList(Array(), $arFilter);
					while ($ob = $res->GetNext()) {
						foreach (self::$KEY as &$u) {
							if (stripos($ob['DESCRIPTION'], $u['TEXT']) !== false) {
								self::$STATS['KEY'][$u['ID']][$group['ID']][$ob['ID']] = true;
								if (self::$URL[$u['URL']]['URL'] != '') {
									$lurl = self::$URL[$u['URL']];
									if (stripos($ob['DESCRIPTION'], $lurl['GLOBAL_URL']) === false && stripos($ob['DESCRIPTION'], $lurl['LOCAL_URL']) === false) {
										self::$ZAMENA[$u['ID']]['S'][$ob['ID']][] = 'DESCRIPTION';
									} else {
										self::$STATS['URLKEY'][$u['ID']][$group['ID']][$ob['ID']] = true;
									}
								}
							}
						}
						unset($u);
						foreach (self::$URL as &$u) {
							if ($u['URL'] != '') {
								if (stripos($ob['DESCRIPTION'], $u['GLOBAL_URL']) !== false || stripos($ob['DESCRIPTION'], $u['LOCAL_URL']) !== false) {
									self::$STATS['URL'][$u['ID']][$group['ID']][$ob['ID']] = true;
								}
							}
							unset($F);
						}
						unset($u);
					}
				}
			}
		}


		$DB->Query("truncate n_seolink_stats;", false, __FILE__ . " > " . __LINE__);
		if (self::$STATS['KEY']) {
			foreach (self::$STATS['KEY'] as $key => $list) {
				foreach ($list as $group => $item) {
					$arFields = array(
						"KEY" => "'" . $DB->ForSql($key) . "'",
						//"URL" => "'" . $DB->ForSql(self::$KEY[$key]['URL']) . "'",
						"GROUP" => "'" . $DB->ForSql($group) . "'",
						"COUNT" => "'" . $DB->ForSql(count($item)) . "'",
						"TYPE" => "'KEY'",
					);
					$DB->Insert("n_seolink_stats", $arFields, $err_mess . __LINE__);
				}
			}
		}
		if (self::$STATS['URL']) {
			foreach (self::$STATS['URL'] as $key => $list) {
				foreach ($list as $group => $item) {
					$arFields = array(
						"URL" => "'" . $DB->ForSql($key) . "'",
						"GROUP" => "'" . $DB->ForSql($group) . "'",
						"COUNT" => "'" . $DB->ForSql(count($item)) . "'",
						"TYPE" => "'URL'",
					);
					$DB->Insert("n_seolink_stats", $arFields, $err_mess . __LINE__);
				}
			}
		}
		if (self::$STATS['KEY']) {
			foreach (self::$STATS['URLKEY'] as $key => $list) {
				foreach ($list as $group => $item) {
					$arFields = array(
						"KEY" => "'" . $DB->ForSql($key) . "'",
						"URL" => "'" . $DB->ForSql(self::$KEY[$key]['URL']) . "'",
						"GROUP" => "'" . $DB->ForSql($group) . "'",
						"COUNT" => "'" . $DB->ForSql(count($item)) . "'",
						"TYPE" => "'KU'",
					);
					$DB->Insert("n_seolink_stats", $arFields, $err_mess . __LINE__);
				}
			}
		}
		if (self::$ZAMENA) {
			$DB->Query("truncate n_seolink_zamena;", false, __FILE__ . " > " . __LINE__);
			foreach (self::$ZAMENA as $key => $list) {
				foreach ($list as $zone => $object) {
					foreach ($object as $n => $fields) {
						foreach ($fields as $f => $field) {
							$arFields = array(
								"KEY" => "'" . $DB->ForSql($key) . "'",
								"ZONE" => "'" . $DB->ForSql($zone) . "'",
								"OBJECT" => "'" . $DB->ForSql($n) . "'",
								"FIELD" => "'" . $DB->ForSql($field) . "'",
							);
							$DB->Insert("n_seolink_zamena", $arFields, $err_mess . __LINE__);
						}
					}
				}
			}
		}
		return '\Nasledie\SeoLink\NAgent::Calc();';
	}

}

?>