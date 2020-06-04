<?

use Bitrix\Main\Localization\Loc as Loc;

Loc::loadLanguageFile(__FILE__);

class nasledie_seolink extends CModule {

	var $MODULE_ID = "nasledie.seolink";
	var $PARTNER_NAME = "Andi SiBmius";
	var $PARTNER_URI = "https://sibmius.org/";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $PARTNER_ID = "";

	/**
	 * ������ ������������, ��� ��������� ������� ������ �������
	 * @var array
	 */
	private $arModuleDependences = array(
		array('main', 'OnBuildGlobalMenu', 'nasledie.seolink', '\Nasledie\SeoLink\Handler', 'main_onBuildGlobalMenu'),
	);

	public function __construct() {
		include(__DIR__ . '/version.php');

		$this->MODULE_DIR = \Bitrix\Main\Loader::getLocal('modules/' . $this->MODULE_ID);

		$this->isLocal = !!strpos($this->MODULE_DIR, '/local/modules/');

		$this->MODULE_NAME = Loc::getMessage('nasledie_seolink_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('nasledie_seolink_MODULE_DESCRIPTION');
		$this->PARTNER_NAME = GetMessage('nasledie_seolink_PARTNER_NAME');
		$this->PARTNER_URI = GetMessage('nasledie_seolink_PARTNER_URI');
		$this->MODULE_VERSION = empty($arModuleVersion['VERSION']) ? '' : $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = empty($arModuleVersion['VERSION_DATE']) ? '' : $arModuleVersion['VERSION_DATE'];
	}

	function DoInstall() {
		RegisterModule($this->MODULE_ID);
		$this->InstallDB();
		$this->InstallFiles();
		$this->InstallAgents();
		$this->InstallDependences();
		$this->RegisterEventType();
		$this->RegisterEventMessage();


		return true;
	}

	function DoUninstall() {
		$this->UnInstallDB();
		$this->UnInstallFiles();
		$this->UnInstallAgents();
		$this->UnInstallDependences();
		$this->UnRegisterEventType();
		$this->UnRegisterEventMessage();

		COption::RemoveOption($this->MODULE_ID);
		UnRegisterModule($this->MODULE_ID);

		return true;
	}

	/**
	 * ���������� � ���� ����������� ������ ��� ������ ������
	 * @return bool
	 */
	function InstallDB() {
		global $DB, $DBType, $APPLICATION;
		//         Database tables creation
		$DB->RunSQLBatch(dirname(__FILE__) . "/db/mysql/install.sql");

		return true;
	}

	/**
	 * �������� ������ ������
	 * @return bool|void
	 */
	function UnInstallDB() {
		global $DB, $DBType, $APPLICATION;

		$DB->RunSQLBatch(dirname(__FILE__) . "/db/mysql/uninstall.sql");

		return true;
	}

	/**
	 * ����������� ������
	 * @return bool|void
	 */
	function InstallFiles($arParams = array()) {

		if (file_exists($path = $this->MODULE_DIR . '/admin')) {
			if ($dir = opendir($path)) {
				while (false !== $item = readdir($dir)) {
					if (in_array($item, array('.', '..', 'menu.php')) || is_dir($path . '/' . $item)) {
						continue;
					}

					if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item)) {
						file_put_contents($file, '<' . '? require($_SERVER["DOCUMENT_ROOT"]."/' . ($this->isLocal ? 'local' : 'bitrix') . '/modules/' . $this->MODULE_ID . '/admin/' . $item . '");?' . '>');
					}
				}
			}
		}
		
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
		
		
		return true;
	}

	/**
	 * �������� ������
	 * @return bool|void
	 */
	function UnInstallFiles() {

		if (file_exists($path = $this->MODULE_DIR . '/admin')) {
			if ($dir = opendir($path)) {
				while (false !== $item = readdir($dir)) {
					if (in_array($item, array('.', '..', 'menu.php')) || is_dir($path . '/' . $item)) {
						continue;
					}

					if (file_exists($file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item)) {
						unlink($file);
					}
				}
			}
		}
		
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
		DeleteDirFilesEx("/bitrix/themes/.default/icons/".$this->MODULE_ID."/");
		return true;
	}

	/**
	 * ��������� �������
	 */
	public function InstallAgents() {
		$oAgent = new CAgent();
		$oAgent->AddAgent('\Nasledie\SeoLink\Agent::UpdateBase();', $this->MODULE_ID, 'N', 86400);
	}

	/**
	 * �������� �������
	 */
	public function UnInstallAgents() {
		$oAgent = new CAgent();
		$oAgent->RemoveModuleAgents($this->MODULE_ID);
	}

	/**
	 * ��������� ������������ �������
	 */
	public function InstallDependences() {
		foreach ($this->arModuleDependences as $item) {
			if (count($item) < 5)
				continue;

			//array('main', 'OnEpilog', 'bxmaker.geoip', '\Bxmaker\GeoIP\Handler', 'min_OnEpilog'),
			RegisterModuleDependences($item[0], $item[1], $item[2], $item[3], $item[4], (isset($item[5]) ? intval($item[5]) : 500));
		}
	}

	/**
	 * �������� ������������ �������
	 */
	public function UnInstallDependences() {
		foreach ($this->arModuleDependences as $item) {
			if (count($item) < 5)
				continue;
			//array('main', 'OnEpilog', 'bxmaker.geoip', '\Bxmaker\GeoIP\Handler', 'main_OnEpilog'),
			UnRegisterModuleDependences($item[0], $item[1], $item[2], $item[3], $item[4]);
		}
	}

	//����������� ����� �������� ��������
	public function RegisterEventType() {
		
	}

	//������ ����� �������� ��������
	function UnRegisterEventType() {
		
	}

	// ����������� �������� ��������
	function RegisterEventMessage() {
		
	}

	//�������� �������� ��������
	function UnRegisterEventMessage() {
		
	}

}
