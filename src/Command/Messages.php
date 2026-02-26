<?php
declare(strict_types=1);

namespace Webman\Validation\Command;

/**
 * Multi-language messages for make:validator command.
 */
final class Messages
{
    /**
     * @return array<string, string> locale => description
     */
    public static function getDescription(): array
    {
        return [
            'zh_CN' => '生成验证器类',
            'zh_TW' => '產生驗證器類',
            'en' => 'Create a validator class',
            'ja' => 'バリデータークラスを作成',
            'ko' => '유효성 검사 클래스 생성',
            'fr' => 'Créer une classe de validation',
            'de' => 'Validierungsklasse erstellen',
            'es' => 'Crear clase de validación',
            'pt_BR' => 'Criar classe de validação',
            'ru' => 'Создать класс валидации',
            'vi' => 'Tạo lớp xác thực',
            'tr' => 'Doğrulama sınıfı oluştur',
            'id' => 'Buat kelas validasi',
            'th' => 'สร้างคลาสตรวจสอบความถูกต้อง',
        ];
    }

    /**
     * @return array<string, string> locale => argument description
     */
    public static function getArgumentName(): array
    {
        return [
            'zh_CN' => '验证器类名（例如：UserValidator、admin/UserValidator）',
            'zh_TW' => '驗證器類名（例如：UserValidator、admin/UserValidator）',
            'en' => 'Validator class name (e.g. UserValidator, admin/UserValidator)',
            'ja' => 'バリデータークラス名（例：UserValidator、admin/UserValidator）',
            'ko' => '유효성 검사 클래스 이름 (예: UserValidator, admin/UserValidator)',
            'fr' => 'Nom de la classe de validation (ex. UserValidator, admin/UserValidator)',
            'de' => 'Name der Validierungsklasse (z. B. UserValidator, admin/UserValidator)',
            'es' => 'Nombre de la clase de validación (ej. UserValidator, admin/UserValidator)',
            'pt_BR' => 'Nome da classe de validação (ex.: UserValidator, admin/UserValidator)',
            'ru' => 'Имя класса валидации (напр. UserValidator, admin/UserValidator)',
            'vi' => 'Tên lớp xác thực (vd: UserValidator, admin/UserValidator)',
            'tr' => 'Doğrulayıcı sınıf adı (örn. UserValidator, admin/UserValidator)',
            'id' => 'Nama kelas validasi (mis. UserValidator, admin/UserValidator)',
            'th' => 'ชื่อคลาสตรวจสอบ (เช่น UserValidator, admin/UserValidator)',
        ];
    }

    /**
     * @return array<string, string> locale => option description
     */
    public static function getOptionPlugin(): array
    {
        return [
            'zh_CN' => '插件名（plugin/ 下的目录名），例如：admin',
            'zh_TW' => '外掛名稱（plugin/ 下的目錄名），例如：admin',
            'en' => 'Plugin name (directory under plugin/). e.g. admin',
            'ja' => 'プラグイン名（plugin/ 以下のディレクトリ名）。例：admin',
            'ko' => '플러그인 이름 (plugin/ 하위 디렉터리). 예: admin',
            'fr' => 'Nom du plugin (répertoire sous plugin/). Ex. : admin',
            'de' => 'Plugin-Name (Unterverzeichnis von plugin/). z. B. admin',
            'es' => 'Nombre del plugin (directorio bajo plugin/). Ej.: admin',
            'pt_BR' => 'Nome do plugin (diretório em plugin/). Ex.: admin',
            'ru' => 'Имя плагина (каталог в plugin/). Напр.: admin',
            'vi' => 'Tên plugin (thư mục trong plugin/). VD: admin',
            'tr' => 'Eklenti adı (plugin/ altındaki dizin). Örn.: admin',
            'id' => 'Nama plugin (direktori di bawah plugin/). Mis.: admin',
            'th' => 'ชื่อปลั๊กอิน (โฟลเดอร์ใต้ plugin/) เช่น admin',
        ];
    }

    /**
     * @return array<string, string> locale => option description
     */
    public static function getOptionPath(): array
    {
        return [
            'zh_CN' => '目标目录（相对项目根目录），例如：plugin/admin/app/validation',
            'zh_TW' => '目標目錄（相對專案根目錄），例如：plugin/admin/app/validation',
            'en' => 'Target directory (relative to project root). e.g. plugin/admin/app/validation',
            'ja' => '出力先ディレクトリ（プロジェクトルートからの相対パス）。例：plugin/admin/app/validation',
            'ko' => '대상 디렉터리 (프로젝트 루트 기준 상대 경로). 예: plugin/admin/app/validation',
            'fr' => 'Répertoire cible (relatif à la racine du projet). Ex. : plugin/admin/app/validation',
            'de' => 'Zielverzeichnis (relativ zum Projektstamm). z. B. plugin/admin/app/validation',
            'es' => 'Directorio destino (relativo a la raíz del proyecto). Ej.: plugin/admin/app/validation',
            'pt_BR' => 'Diretório de destino (relativo à raiz do projeto). Ex.: plugin/admin/app/validation',
            'ru' => 'Целевой каталог (относительно корня проекта). Напр.: plugin/admin/app/validation',
            'vi' => 'Thư mục đích (tương đối so với thư mục gốc dự án). VD: plugin/admin/app/validation',
            'tr' => 'Hedef dizin (proje köküne göre). Örn.: plugin/admin/app/validation',
            'id' => 'Direktori tujuan (relatif ke root proyek). Mis.: plugin/admin/app/validation',
            'th' => 'โฟลเดอร์ปลายทาง (สัมพันธ์กับรากโปรเจกต์) เช่น plugin/admin/app/validation',
        ];
    }

    /**
     * @return array<string, string> locale => option description
     */
    public static function getOptionForce(): array
    {
        return [
            'zh_CN' => '文件已存在时强制覆盖',
            'zh_TW' => '檔案已存在時強制覆蓋',
            'en' => 'Overwrite if file already exists',
            'ja' => 'ファイルが既に存在する場合に上書き',
            'ko' => '파일이 이미 있으면 덮어쓰기',
            'fr' => 'Écraser si le fichier existe déjà',
            'de' => 'Überschreiben, wenn die Datei bereits existiert',
            'es' => 'Sobrescribir si el archivo ya existe',
            'pt_BR' => 'Sobrescrever se o arquivo já existir',
            'ru' => 'Перезаписать, если файл уже существует',
            'vi' => 'Ghi đè nếu tệp đã tồn tại',
            'tr' => 'Dosya zaten varsa üzerine yaz',
            'id' => 'Timpa jika berkas sudah ada',
            'th' => 'เขียนทับถ้ามีไฟล์อยู่แล้ว',
        ];
    }

    /**
     * @return array<string, string> locale => option description
     */
    public static function getOptionTable(): array
    {
        return [
            'zh_CN' => '从数据库表推断并生成规则（例如：users）',
            'zh_TW' => '從資料庫資料表推斷並產生規則（例如：users）',
            'en' => 'Generate rules from database table (e.g. users)',
            'ja' => 'データベーステーブルからルールを推論して生成（例：users）',
            'ko' => '데이터베이스 테이블에서 규칙 생성 (예: users)',
            'fr' => 'Générer les règles à partir d\'une table (ex. : users)',
            'de' => 'Regeln aus Datenbanktabelle ableiten (z. B. users)',
            'es' => 'Generar reglas desde la tabla de base de datos (ej.: users)',
            'pt_BR' => 'Gerar regras a partir da tabela do banco (ex.: users)',
            'ru' => 'Сформировать правила по таблице БД (напр. users)',
            'vi' => 'Sinh quy tắc từ bảng cơ sở dữ liệu (vd: users)',
            'tr' => 'Veritabanı tablosundan kurallar üret (örn.: users)',
            'id' => 'Hasilkan aturan dari tabel database (mis. users)',
            'th' => 'สร้างกฎจากตารางฐานข้อมูล (เช่น users)',
        ];
    }

    /**
     * @return array<string, string> locale => option description
     */
    public static function getOptionDatabase(): array
    {
        return [
            'zh_CN' => '数据库连接名',
            'zh_TW' => '資料庫連線名稱',
            'en' => 'Database connection name',
            'ja' => 'データベース接続名',
            'ko' => '데이터베이스 연결 이름',
            'fr' => 'Nom de la connexion à la base de données',
            'de' => 'Datenbankverbindungsname',
            'es' => 'Nombre de la conexión a la base de datos',
            'pt_BR' => 'Nome da conexão do banco de dados',
            'ru' => 'Имя подключения к БД',
            'vi' => 'Tên kết nối cơ sở dữ liệu',
            'tr' => 'Veritabanı bağlantı adı',
            'id' => 'Nama koneksi database',
            'th' => 'ชื่อการเชื่อมต่อฐานข้อมูล',
        ];
    }

    /**
     * @return array<string, string> locale => option description
     */
    public static function getOptionScenes(): array
    {
        return [
            'zh_CN' => '生成场景（支持：crud）',
            'zh_TW' => '產生場景（支援：crud）',
            'en' => 'Generate scenes (supported: crud)',
            'ja' => 'シーンを生成（対応：crud）',
            'ko' => '장면 생성 (지원: crud)',
            'fr' => 'Générer des scènes (supporté : crud)',
            'de' => 'Szenen erzeugen (unterstützt: crud)',
            'es' => 'Generar escenas (soportado: crud)',
            'pt_BR' => 'Gerar cenas (suportado: crud)',
            'ru' => 'Создать сцены (поддержка: crud)',
            'vi' => 'Tạo cảnh (hỗ trợ: crud)',
            'tr' => 'Sahneleri oluştur (desteklenen: crud)',
            'id' => 'Hasilkan adegan (didukung: crud)',
            'th' => 'สร้างฉาก (รองรับ: crud)',
        ];
    }

    /**
     * @return array<string, string> locale => option description
     */
    public static function getOptionOrm(): array
    {
        return [
            'zh_CN' => '使用的 ORM：auto|laravel|thinkorm（默认：auto）',
            'zh_TW' => '使用的 ORM：auto|laravel|thinkorm（預設：auto）',
            'en' => 'ORM to use: auto|laravel|thinkorm (default: auto)',
            'ja' => '使用する ORM：auto|laravel|thinkorm（既定：auto）',
            'ko' => '사용할 ORM: auto|laravel|thinkorm (기본: auto)',
            'fr' => 'ORM à utiliser : auto|laravel|thinkorm (défaut : auto)',
            'de' => 'Zu verwendende ORM: auto|laravel|thinkorm (Standard: auto)',
            'es' => 'ORM a usar: auto|laravel|thinkorm (por defecto: auto)',
            'pt_BR' => 'ORM a usar: auto|laravel|thinkorm (padrão: auto)',
            'ru' => 'ORM: auto|laravel|thinkorm (по умолчанию: auto)',
            'vi' => 'ORM sử dụng: auto|laravel|thinkorm (mặc định: auto)',
            'tr' => 'Kullanılacak ORM: auto|laravel|thinkorm (varsayılan: auto)',
            'id' => 'ORM yang digunakan: auto|laravel|thinkorm (baku: auto)',
            'th' => 'ORM ที่ใช้: auto|laravel|thinkorm (ค่าเริ่มต้น: auto)',
        ];
    }

    /**
     * CLI messages (with Symfony console tags). locale => [ key => text ]
     *
     * @return array<string, array<string, string>>
     */
    public static function getCliMessages(): array
    {
        return [
            'zh_CN' => [
                'invalid_name_empty' => '<error>验证器类名不能为空。</error>',
                'invalid_name' => '<error>验证器类名无效：{name}</error>',
                'invalid_plugin' => '<error>插件名无效：{plugin}。`--plugin/-p` 只能是 plugin/ 目录下的目录名，不能包含 / 或 \\。</error>',
                'plugin_not_found' => "<error>插件不存在：</error> <comment>{plugin}</comment>\n请检查插件名是否输入正确，或确认插件已正确安装/启用。",
                'plugin_path_conflict' => "<error>`--path/-P` 指定的路径不在 plugin/{plugin}/ 目录下。\n同时使用 `--plugin/-p` 时，`--path/-P` 必须是 plugin/{plugin}/ 下的路径。</error>",
                'invalid_path' => '<error>路径无效：{path}。`--path/-P` 必须是相对路径（相对于项目根目录），不能是绝对路径。</error>',
                'file_exists' => '<error>文件已存在：</error> {path}',
                'override_prompt' => "<question>文件已存在：{path}</question>\n<question>是否覆盖？[Y/n]（回车=Y）</question>\n",
                'use_force' => '使用 <comment>--force/-f</comment> 强制覆盖。',
                'scenes_requires_table' => '<error>选项 --scenes 需要同时指定 --table。</error>',
                'unsupported_orm' => '<error>不支持的 ORM：{orm}（支持：auto/laravel/thinkorm）。</error>',
                'database_connection_not_found' => '<error>数据库连接不存在：</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>无法从数据表推断出规则：</error> {table}',
                'failed_generate_from_table' => '<error>从数据表生成验证器失败：</error> {table}',
                'failed_write_file' => '<error>写入文件失败：</error> {path}',
                'reason' => '<comment>原因：</comment> {reason}',
                'created' => '<info>已创建：</info> {path}',
                'class' => '<info>类：</info> {class}',
                'table' => '<info>数据表：</info> {table}',
                'rules_count' => '<info>规则数：</info> {count}',
                'scenes_count' => '<info>场景数：</info> {count}',
            ],
            'zh_TW' => [
                'invalid_name_empty' => '<error>驗證器類名不能為空。</error>',
                'invalid_name' => '<error>驗證器類名無效：{name}</error>',
                'invalid_plugin' => '<error>外掛名稱無效：{plugin}。`--plugin/-p` 只能是 plugin/ 目錄下的目錄名，不能包含 / 或 \\。</error>',
                'plugin_not_found' => "<error>外掛不存在：</error> <comment>{plugin}</comment>\n請檢查外掛名稱是否正確，或確認外掛已正確安裝/啟用。",
                'plugin_path_conflict' => "<error>`--path/-P` 指定的路徑不在 plugin/{plugin}/ 目錄下。\n同時使用 `--plugin/-p` 時，`--path/-P` 必須是 plugin/{plugin}/ 下的路徑。</error>",
                'invalid_path' => '<error>路徑無效：{path}。`--path/-P` 必須是相對路徑（相對於專案根目錄），不能是絕對路徑。</error>',
                'file_exists' => '<error>檔案已存在：</error> {path}',
                'override_prompt' => "<question>檔案已存在：{path}</question>\n<question>是否覆蓋？[Y/n]（Enter=Y）</question>\n",
                'use_force' => '使用 <comment>--force/-f</comment> 強制覆蓋。',
                'scenes_requires_table' => '<error>選項 --scenes 需要同時指定 --table。</error>',
                'unsupported_orm' => '<error>不支援的 ORM：{orm}（支援：auto/laravel/thinkorm）。</error>',
                'database_connection_not_found' => '<error>資料庫連線不存在：</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>無法從資料表推斷出規則：</error> {table}',
                'failed_generate_from_table' => '<error>從資料表產生驗證器失敗：</error> {table}',
                'failed_write_file' => '<error>寫入檔案失敗：</error> {path}',
                'reason' => '<comment>原因：</comment> {reason}',
                'created' => '<info>已建立：</info> {path}',
                'class' => '<info>類別：</info> {class}',
                'table' => '<info>資料表：</info> {table}',
                'rules_count' => '<info>規則數：</info> {count}',
                'scenes_count' => '<info>場景數：</info> {count}',
            ],
            'en' => [
                'invalid_name_empty' => '<error>Validator name cannot be empty.</error>',
                'invalid_name' => '<error>Invalid validator name: {name}</error>',
                'invalid_plugin' => '<error>Invalid plugin name: {plugin}. `--plugin/-p` must be a directory name under plugin/ and must not contain / or \\.</error>',
                'plugin_not_found' => "<error>Plugin not found:</error> <comment>{plugin}</comment>\nPlease check the plugin name, or ensure the plugin is properly installed/enabled.",
                'plugin_path_conflict' => "<error>`--path/-P` is not under plugin/{plugin}/.\nWhen `--plugin/-p` is specified, `--path/-P` must be under plugin/{plugin}/.</error>",
                'invalid_path' => '<error>Invalid path: {path}. `--path/-P` must be a relative path (to project root) and must not be an absolute path.</error>',
                'file_exists' => '<error>File already exists:</error> {path}',
                'override_prompt' => "<question>File already exists: {path}</question>\n<question>Overwrite? [Y/n] (Enter = Y)</question>\n",
                'use_force' => 'Use <comment>--force/-f</comment> to overwrite.',
                'scenes_requires_table' => '<error>Option --scenes requires --table.</error>',
                'unsupported_orm' => '<error>Unsupported ORM: {orm} (supported: auto/laravel/thinkorm).</error>',
                'database_connection_not_found' => '<error>Database connection not found:</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>No rules inferred from table:</error> {table}',
                'failed_generate_from_table' => '<error>Failed to generate validator from table:</error> {table}',
                'failed_write_file' => '<error>Failed to write file:</error> {path}',
                'reason' => '<comment>Reason:</comment> {reason}',
                'created' => '<info>Created:</info> {path}',
                'class' => '<info>Class:</info> {class}',
                'table' => '<info>Table:</info> {table}',
                'rules_count' => '<info>Rules:</info> {count}',
                'scenes_count' => '<info>Scenes:</info> {count}',
            ],
            'ja' => [
                'invalid_name_empty' => '<error>バリデーター名を空にできません。</error>',
                'invalid_name' => '<error>無効なバリデーター名：{name}</error>',
                'invalid_plugin' => '<error>無効なプラグイン名：{plugin}。`--plugin/-p` は plugin/ 以下のディレクトリ名のみ指定でき、/ または \\ を含めません。</error>',
                'plugin_not_found' => "<error>プラグインが見つかりません：</error> <comment>{plugin}</comment>\nプラグイン名を確認するか、正しくインストール/有効化されているか確認してください。",
                'plugin_path_conflict' => "<error>`--path/-P` が plugin/{plugin}/ 配下にありません。\n`--plugin/-p` 指定時、`--path/-P` は plugin/{plugin}/ 配下のパスでなければなりません。</error>",
                'invalid_path' => '<error>無効なパス：{path}。`--path/-P` はプロジェクトルートからの相対パスで、絶対パスは指定できません。</error>',
                'file_exists' => '<error>ファイルは既に存在します：</error> {path}',
                'override_prompt' => "<question>ファイルは既に存在します：{path}</question>\n<question>上書きしますか？[Y/n]（Enter=Y）</question>\n",
                'use_force' => '<comment>--force/-f</comment> で上書きしてください。',
                'scenes_requires_table' => '<error>オプション --scenes には --table の指定が必要です。</error>',
                'unsupported_orm' => '<error>サポートされていない ORM：{orm}（対応：auto/laravel/thinkorm）。</error>',
                'database_connection_not_found' => '<error>データベース接続が見つかりません：</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>テーブルからルールを推論できません：</error> {table}',
                'failed_generate_from_table' => '<error>テーブルからバリデーターの生成に失敗しました：</error> {table}',
                'failed_write_file' => '<error>ファイルの書き込みに失敗しました：</error> {path}',
                'reason' => '<comment>理由：</comment> {reason}',
                'created' => '<info>作成しました：</info> {path}',
                'class' => '<info>クラス：</info> {class}',
                'table' => '<info>テーブル：</info> {table}',
                'rules_count' => '<info>ルール数：</info> {count}',
                'scenes_count' => '<info>シーン数：</info> {count}',
            ],
            'ko' => [
                'invalid_name_empty' => '<error>유효성 검사 클래스 이름을 비워 둘 수 없습니다.</error>',
                'invalid_name' => '<error>잘못된 유효성 검사 클래스 이름: {name}</error>',
                'invalid_plugin' => '<error>잘못된 플러그인 이름: {plugin}. `--plugin/-p`는 plugin/ 아래의 디렉터리 이름만 가능하며 / 또는 \\를 포함할 수 없습니다.</error>',
                'plugin_not_found' => "<error>플러그인을 찾을 수 없습니다:</error> <comment>{plugin}</comment>\n플러그인 이름을 확인하거나, 올바르게 설치/활성화되었는지 확인하세요.",
                'plugin_path_conflict' => "<error>`--path/-P`가 plugin/{plugin}/ 아래에 없습니다.\n`--plugin/-p` 지정 시 `--path/-P`는 plugin/{plugin}/ 하위 경로여야 합니다.</error>",
                'invalid_path' => '<error>잘못된 경로: {path}. `--path/-P`는 프로젝트 루트 기준 상대 경로여야 하며 절대 경로는 사용할 수 없습니다.</error>',
                'file_exists' => '<error>파일이 이미 존재합니다:</error> {path}',
                'override_prompt' => "<question>파일이 이미 존재합니다: {path}</question>\n<question>덮어쓰시겠습니까? [Y/n] (Enter=Y)</question>\n",
                'use_force' => '<comment>--force/-f</comment>로 덮어쓰세요.',
                'scenes_requires_table' => '<error>--scenes 옵션에는 --table 지정이 필요합니다.</error>',
                'unsupported_orm' => '<error>지원하지 않는 ORM: {orm} (지원: auto/laravel/thinkorm).</error>',
                'database_connection_not_found' => '<error>데이터베이스 연결을 찾을 수 없습니다:</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>테이블에서 규칙을 추론할 수 없습니다:</error> {table}',
                'failed_generate_from_table' => '<error>테이블에서 유효성 검사 클래스 생성에 실패했습니다:</error> {table}',
                'failed_write_file' => '<error>파일 쓰기에 실패했습니다:</error> {path}',
                'reason' => '<comment>사유:</comment> {reason}',
                'created' => '<info>생성됨:</info> {path}',
                'class' => '<info>클래스:</info> {class}',
                'table' => '<info>테이블:</info> {table}',
                'rules_count' => '<info>규칙 수:</info> {count}',
                'scenes_count' => '<info>장면 수:</info> {count}',
            ],
            'fr' => [
                'invalid_name_empty' => '<error>Le nom du validateur ne peut pas être vide.</error>',
                'invalid_name' => '<error>Nom de validateur invalide : {name}</error>',
                'invalid_plugin' => '<error>Nom de plugin invalide : {plugin}. `--plugin/-p` doit être un nom de répertoire sous plugin/ et ne doit pas contenir / ou \\.</error>',
                'plugin_not_found' => "<error>Plugin introuvable :</error> <comment>{plugin}</comment>\nVérifiez le nom du plugin ou assurez-vous qu'il est correctement installé/activé.",
                'plugin_path_conflict' => "<error>`--path/-P` n'est pas sous plugin/{plugin}/.\nAvec `--plugin/-p`, `--path/-P` doit être un chemin sous plugin/{plugin}/.</error>",
                'invalid_path' => '<error>Chemin invalide : {path}. `--path/-P` doit être un chemin relatif (à la racine du projet), pas un chemin absolu.</error>',
                'file_exists' => '<error>Le fichier existe déjà :</error> {path}',
                'override_prompt' => "<question>Le fichier existe déjà : {path}</question>\n<question>Écraser ? [Y/n] (Entrée = Y)</question>\n",
                'use_force' => 'Utilisez <comment>--force/-f</comment> pour écraser.',
                'scenes_requires_table' => '<error>L\'option --scenes nécessite --table.</error>',
                'unsupported_orm' => '<error>ORM non pris en charge : {orm} (pris en charge : auto/laravel/thinkorm).</error>',
                'database_connection_not_found' => '<error>Connexion à la base de données introuvable :</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>Aucune règle déduite de la table :</error> {table}',
                'failed_generate_from_table' => '<error>Échec de la génération du validateur à partir de la table :</error> {table}',
                'failed_write_file' => '<error>Échec de l\'écriture du fichier :</error> {path}',
                'reason' => '<comment>Raison :</comment> {reason}',
                'created' => '<info>Créé :</info> {path}',
                'class' => '<info>Classe :</info> {class}',
                'table' => '<info>Table :</info> {table}',
                'rules_count' => '<info>Règles :</info> {count}',
                'scenes_count' => '<info>Scènes :</info> {count}',
            ],
            'de' => [
                'invalid_name_empty' => '<error>Der Name des Validators darf nicht leer sein.</error>',
                'invalid_name' => '<error>Ungültiger Validator-Name: {name}</error>',
                'invalid_plugin' => '<error>Ungültiger Plugin-Name: {plugin}. `--plugin/-p` muss ein Verzeichnisname unter plugin/ sein und darf / oder \\ nicht enthalten.</error>',
                'plugin_not_found' => "<error>Plugin nicht gefunden:</error> <comment>{plugin}</comment>\nBitte prüfen Sie den Plugin-Namen oder stellen Sie sicher, dass das Plugin korrekt installiert/aktiviert ist.",
                'plugin_path_conflict' => "<error>`--path/-P` liegt nicht unter plugin/{plugin}/.\nBei Angabe von `--plugin/-p` muss `--path/-P` unter plugin/{plugin}/ liegen.</error>",
                'invalid_path' => '<error>Ungültiger Pfad: {path}. `--path/-P` muss ein relativer Pfad (zum Projektstamm) sein, kein absoluter Pfad.</error>',
                'file_exists' => '<error>Datei existiert bereits:</error> {path}',
                'override_prompt' => "<question>Datei existiert bereits: {path}</question>\n<question>Überschreiben? [Y/n] (Eingabe = Y)</question>\n",
                'use_force' => 'Mit <comment>--force/-f</comment> überschreiben.',
                'scenes_requires_table' => '<error>Option --scenes erfordert --table.</error>',
                'unsupported_orm' => '<error>Nicht unterstützte ORM: {orm} (unterstützt: auto/laravel/thinkorm).</error>',
                'database_connection_not_found' => '<error>Datenbankverbindung nicht gefunden:</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>Keine Regeln aus Tabelle abgeleitet:</error> {table}',
                'failed_generate_from_table' => '<error>Validator konnte aus Tabelle nicht erzeugt werden:</error> {table}',
                'failed_write_file' => '<error>Datei konnte nicht geschrieben werden:</error> {path}',
                'reason' => '<comment>Grund:</comment> {reason}',
                'created' => '<info>Erstellt:</info> {path}',
                'class' => '<info>Klasse:</info> {class}',
                'table' => '<info>Tabelle:</info> {table}',
                'rules_count' => '<info>Regeln:</info> {count}',
                'scenes_count' => '<info>Szenen:</info> {count}',
            ],
            'es' => [
                'invalid_name_empty' => '<error>El nombre del validador no puede estar vacío.</error>',
                'invalid_name' => '<error>Nombre de validador no válido: {name}</error>',
                'invalid_plugin' => '<error>Nombre de plugin no válido: {plugin}. `--plugin/-p` debe ser un nombre de directorio bajo plugin/ y no puede contener / ni \\.</error>',
                'plugin_not_found' => "<error>Plugin no encontrado:</error> <comment>{plugin}</comment>\nCompruebe el nombre del plugin o asegúrese de que está correctamente instalado/habilitado.",
                'plugin_path_conflict' => "<error>`--path/-P` no está bajo plugin/{plugin}/.\nAl usar `--plugin/-p`, `--path/-P` debe ser una ruta bajo plugin/{plugin}/.</error>",
                'invalid_path' => '<error>Ruta no válida: {path}. `--path/-P` debe ser una ruta relativa (a la raíz del proyecto), no absoluta.</error>',
                'file_exists' => '<error>El archivo ya existe:</error> {path}',
                'override_prompt' => "<question>El archivo ya existe: {path}</question>\n<question>¿Sobrescribir? [Y/n] (Enter = Y)</question>\n",
                'use_force' => 'Use <comment>--force/-f</comment> para sobrescribir.',
                'scenes_requires_table' => '<error>La opción --scenes requiere --table.</error>',
                'unsupported_orm' => '<error>ORM no soportada: {orm} (soportadas: auto/laravel/thinkorm).</error>',
                'database_connection_not_found' => '<error>Conexión a la base de datos no encontrada:</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>No se pudieron inferir reglas de la tabla:</error> {table}',
                'failed_generate_from_table' => '<error>Error al generar el validador desde la tabla:</error> {table}',
                'failed_write_file' => '<error>Error al escribir el archivo:</error> {path}',
                'reason' => '<comment>Motivo:</comment> {reason}',
                'created' => '<info>Creado:</info> {path}',
                'class' => '<info>Clase:</info> {class}',
                'table' => '<info>Tabla:</info> {table}',
                'rules_count' => '<info>Reglas:</info> {count}',
                'scenes_count' => '<info>Escenas:</info> {count}',
            ],
            'pt_BR' => [
                'invalid_name_empty' => '<error>O nome do validador não pode estar vazio.</error>',
                'invalid_name' => '<error>Nome de validador inválido: {name}</error>',
                'invalid_plugin' => '<error>Nome de plugin inválido: {plugin}. `--plugin/-p` deve ser um nome de diretório em plugin/ e não pode conter / ou \\.</error>',
                'plugin_not_found' => "<error>Plugin não encontrado:</error> <comment>{plugin}</comment>\nVerifique o nome do plugin ou confira se está instalado/ativado corretamente.",
                'plugin_path_conflict' => "<error>`--path/-P` não está em plugin/{plugin}/.\nAo usar `--plugin/-p`, `--path/-P` deve estar sob plugin/{plugin}/.</error>",
                'invalid_path' => '<error>Caminho inválido: {path}. `--path/-P` deve ser um caminho relativo (à raiz do projeto), não absoluto.</error>',
                'file_exists' => '<error>O arquivo já existe:</error> {path}',
                'override_prompt' => "<question>O arquivo já existe: {path}</question>\n<question>Sobrescrever? [Y/n] (Enter = Y)</question>\n",
                'use_force' => 'Use <comment>--force/-f</comment> para sobrescrever.',
                'scenes_requires_table' => '<error>A opção --scenes exige --table.</error>',
                'unsupported_orm' => '<error>ORM não suportada: {orm} (suportadas: auto/laravel/thinkorm).</error>',
                'database_connection_not_found' => '<error>Conexão com o banco não encontrada:</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>Nenhuma regra inferida da tabela:</error> {table}',
                'failed_generate_from_table' => '<error>Falha ao gerar validador a partir da tabela:</error> {table}',
                'failed_write_file' => '<error>Falha ao escrever o arquivo:</error> {path}',
                'reason' => '<comment>Motivo:</comment> {reason}',
                'created' => '<info>Criado:</info> {path}',
                'class' => '<info>Classe:</info> {class}',
                'table' => '<info>Tabela:</info> {table}',
                'rules_count' => '<info>Regras:</info> {count}',
                'scenes_count' => '<info>Cenas:</info> {count}',
            ],
            'ru' => [
                'invalid_name_empty' => '<error>Имя класса валидации не может быть пустым.</error>',
                'invalid_name' => '<error>Недопустимое имя валидатора: {name}</error>',
                'invalid_plugin' => '<error>Недопустимое имя плагина: {plugin}. Для `--plugin/-p` допустимо только имя каталога в plugin/, без / и \\.</error>',
                'plugin_not_found' => "<error>Плагин не найден:</error> <comment>{plugin}</comment>\nПроверьте имя плагина или убедитесь, что он установлен и включён.",
                'plugin_path_conflict' => "<error>`--path/-P` не находится в plugin/{plugin}/.\nПри использовании `--plugin/-p` `--path/-P` должен быть путём внутри plugin/{plugin}/.</error>",
                'invalid_path' => '<error>Недопустимый путь: {path}. Для `--path/-P` нужен относительный путь (от корня проекта), не абсолютный.</error>',
                'file_exists' => '<error>Файл уже существует:</error> {path}',
                'override_prompt' => "<question>Файл уже существует: {path}</question>\n<question>Перезаписать? [Y/n] (Enter = Y)</question>\n",
                'use_force' => 'Используйте <comment>--force/-f</comment> для перезаписи.',
                'scenes_requires_table' => '<error>Для опции --scenes необходимо указать --table.</error>',
                'unsupported_orm' => '<error>Неподдерживаемая ORM: {orm} (поддерживаются: auto/laravel/thinkorm).</error>',
                'database_connection_not_found' => '<error>Подключение к БД не найдено:</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>Не удалось вывести правила по таблице:</error> {table}',
                'failed_generate_from_table' => '<error>Не удалось сформировать валидатор по таблице:</error> {table}',
                'failed_write_file' => '<error>Не удалось записать файл:</error> {path}',
                'reason' => '<comment>Причина:</comment> {reason}',
                'created' => '<info>Создано:</info> {path}',
                'class' => '<info>Класс:</info> {class}',
                'table' => '<info>Таблица:</info> {table}',
                'rules_count' => '<info>Правил:</info> {count}',
                'scenes_count' => '<info>Сцен:</info> {count}',
            ],
            'vi' => [
                'invalid_name_empty' => '<error>Tên lớp xác thực không được để trống.</error>',
                'invalid_name' => '<error>Tên lớp xác thực không hợp lệ: {name}</error>',
                'invalid_plugin' => '<error>Tên plugin không hợp lệ: {plugin}. `--plugin/-p` phải là tên thư mục trong plugin/ và không được chứa / hoặc \\.</error>',
                'plugin_not_found' => "<error>Không tìm thấy plugin:</error> <comment>{plugin}</comment>\nVui lòng kiểm tra tên plugin hoặc đảm bảo plugin đã được cài đặt/bật đúng cách.",
                'plugin_path_conflict' => "<error>`--path/-P` không nằm trong plugin/{plugin}/.\nKhi dùng `--plugin/-p`, `--path/-P` phải là đường dẫn trong plugin/{plugin}/.</error>",
                'invalid_path' => '<error>Đường dẫn không hợp lệ: {path}. `--path/-P` phải là đường dẫn tương đối (so với thư mục gốc dự án), không phải đường dẫn tuyệt đối.</error>',
                'file_exists' => '<error>Tệp đã tồn tại:</error> {path}',
                'override_prompt' => "<question>Tệp đã tồn tại: {path}</question>\n<question>Ghi đè? [Y/n] (Enter = Y)</question>\n",
                'use_force' => 'Dùng <comment>--force/-f</comment> để ghi đè.',
                'scenes_requires_table' => '<error>Tùy chọn --scenes yêu cầu --table.</error>',
                'unsupported_orm' => '<error>ORM không được hỗ trợ: {orm} (hỗ trợ: auto/laravel/thinkorm).</error>',
                'database_connection_not_found' => '<error>Không tìm thấy kết nối cơ sở dữ liệu:</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>Không suy ra được quy tắc từ bảng:</error> {table}',
                'failed_generate_from_table' => '<error>Không thể tạo lớp xác thực từ bảng:</error> {table}',
                'failed_write_file' => '<error>Không thể ghi tệp:</error> {path}',
                'reason' => '<comment>Lý do:</comment> {reason}',
                'created' => '<info>Đã tạo:</info> {path}',
                'class' => '<info>Lớp:</info> {class}',
                'table' => '<info>Bảng:</info> {table}',
                'rules_count' => '<info>Số quy tắc:</info> {count}',
                'scenes_count' => '<info>Số cảnh:</info> {count}',
            ],
            'tr' => [
                'invalid_name_empty' => '<error>Doğrulayıcı adı boş bırakılamaz.</error>',
                'invalid_name' => '<error>Geçersiz doğrulayıcı adı: {name}</error>',
                'invalid_plugin' => '<error>Geçersiz eklenti adı: {plugin}. `--plugin/-p` yalnızca plugin/ altındaki bir dizin adı olmalı, / veya \\ içeremez.</error>',
                'plugin_not_found' => "<error>Eklenti bulunamadı:</error> <comment>{plugin}</comment>\nEklenti adını kontrol edin veya doğru kurulduğundan/etkinleştirildiğinden emin olun.",
                'plugin_path_conflict' => "<error>`--path/-P` plugin/{plugin}/ altında değil.\n`--plugin/-p` belirtildiğinde `--path/-P` plugin/{plugin}/ altında bir yol olmalıdır.</error>",
                'invalid_path' => '<error>Geçersiz yol: {path}. `--path/-P` proje köküne göre göreli yol olmalı, mutlak yol olmamalı.</error>',
                'file_exists' => '<error>Dosya zaten mevcut:</error> {path}',
                'override_prompt' => "<question>Dosya zaten mevcut: {path}</question>\n<question>Üzerine yazılsın mı? [Y/n] (Enter = Y)</question>\n",
                'use_force' => 'Üzerine yazmak için <comment>--force/-f</comment> kullanın.',
                'scenes_requires_table' => '<error>--scenes seçeneği --table gerektirir.</error>',
                'unsupported_orm' => '<error>Desteklenmeyen ORM: {orm} (desteklenen: auto/laravel/thinkorm).</error>',
                'database_connection_not_found' => '<error>Veritabanı bağlantısı bulunamadı:</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>Tablodan kural çıkarılamadı:</error> {table}',
                'failed_generate_from_table' => '<error>Tablodan doğrulayıcı oluşturulamadı:</error> {table}',
                'failed_write_file' => '<error>Dosya yazılamadı:</error> {path}',
                'reason' => '<comment>Neden:</comment> {reason}',
                'created' => '<info>Oluşturuldu:</info> {path}',
                'class' => '<info>Sınıf:</info> {class}',
                'table' => '<info>Tablo:</info> {table}',
                'rules_count' => '<info>Kural sayısı:</info> {count}',
                'scenes_count' => '<info>Sahne sayısı:</info> {count}',
            ],
            'id' => [
                'invalid_name_empty' => '<error>Nama validator tidak boleh kosong.</error>',
                'invalid_name' => '<error>Nama validator tidak valid: {name}</error>',
                'invalid_plugin' => '<error>Nama plugin tidak valid: {plugin}. `--plugin/-p` harus nama direktori di bawah plugin/ dan tidak boleh berisi / atau \\.</error>',
                'plugin_not_found' => "<error>Plugin tidak ditemukan:</error> <comment>{plugin}</comment>\nPeriksa nama plugin atau pastikan plugin terpasang/diaktifkan dengan benar.",
                'plugin_path_conflict' => "<error>`--path/-P` tidak berada di bawah plugin/{plugin}/.\nSaat `--plugin/-p` ditentukan, `--path/-P` harus di bawah plugin/{plugin}/.</error>",
                'invalid_path' => '<error>Path tidak valid: {path}. `--path/-P` harus path relatif (ke root proyek), bukan path absolut.</error>',
                'file_exists' => '<error>Berkas sudah ada:</error> {path}',
                'override_prompt' => "<question>Berkas sudah ada: {path}</question>\n<question>Timpa? [Y/n] (Enter = Y)</question>\n",
                'use_force' => 'Gunakan <comment>--force/-f</comment> untuk menimpa.',
                'scenes_requires_table' => '<error>Opsi --scenes memerlukan --table.</error>',
                'unsupported_orm' => '<error>ORM tidak didukung: {orm} (didukung: auto/laravel/thinkorm).</error>',
                'database_connection_not_found' => '<error>Koneksi database tidak ditemukan:</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>Tidak ada aturan yang disimpulkan dari tabel:</error> {table}',
                'failed_generate_from_table' => '<error>Gagal membuat validator dari tabel:</error> {table}',
                'failed_write_file' => '<error>Gagal menulis berkas:</error> {path}',
                'reason' => '<comment>Alasan:</comment> {reason}',
                'created' => '<info>Dibuat:</info> {path}',
                'class' => '<info>Kelas:</info> {class}',
                'table' => '<info>Tabel:</info> {table}',
                'rules_count' => '<info>Jumlah aturan:</info> {count}',
                'scenes_count' => '<info>Jumlah adegan:</info> {count}',
            ],
            'th' => [
                'invalid_name_empty' => '<error>ชื่อคลาสตรวจสอบไม่สามารถเว้นว่างได้</error>',
                'invalid_name' => '<error>ชื่อคลาสตรวจสอบไม่ถูกต้อง: {name}</error>',
                'invalid_plugin' => '<error>ชื่อปลั๊กอินไม่ถูกต้อง: {plugin} สำหรับ `--plugin/-p` ต้องเป็นชื่อโฟลเดอร์ภายใต้ plugin/ และห้ามมี / หรือ \\</error>',
                'plugin_not_found' => "<error>ไม่พบปลั๊กอิน:</error> <comment>{plugin}</comment>\nกรุณาตรวจสอบชื่อปลั๊กอิน หรือตรวจสอบว่าติดตั้ง/เปิดใช้แล้วอย่างถูกต้อง",
                'plugin_path_conflict' => "<error>`--path/-P` ไม่อยู่ภายใต้ plugin/{plugin}/\nเมื่อระบุ `--plugin/-p` `--path/-P` ต้องเป็นเส้นทางภายใต้ plugin/{plugin}/</error>",
                'invalid_path' => '<error>เส้นทางไม่ถูกต้อง: {path} สำหรับ `--path/-P` ต้องเป็นเส้นทางสัมพัทธ์ (จากรากโปรเจกต์) ไม่ใช่เส้นทางสัมบูรณ์</error>',
                'file_exists' => '<error>มีไฟล์อยู่แล้ว:</error> {path}',
                'override_prompt' => "<question>มีไฟล์อยู่แล้ว: {path}</question>\n<question>เขียนทับหรือไม่? [Y/n] (Enter = Y)</question>\n",
                'use_force' => 'ใช้ <comment>--force/-f</comment> เพื่อเขียนทับ',
                'scenes_requires_table' => '<error>ตัวเลือก --scenes ต้องใช้ร่วมกับ --table</error>',
                'unsupported_orm' => '<error>ไม่รองรับ ORM: {orm} (รองรับ: auto/laravel/thinkorm)</error>',
                'database_connection_not_found' => '<error>ไม่พบการเชื่อมต่อฐานข้อมูล:</error> <comment>{connection}</comment>',
                'no_rules_from_table' => '<error>ไม่สามารถสรุปกฎจากตารางได้:</error> {table}',
                'failed_generate_from_table' => '<error>สร้างคลาสตรวจสอบจากตารางไม่สำเร็จ:</error> {table}',
                'failed_write_file' => '<error>เขียนไฟล์ไม่สำเร็จ:</error> {path}',
                'reason' => '<comment>สาเหตุ:</comment> {reason}',
                'created' => '<info>สร้างแล้ว:</info> {path}',
                'class' => '<info>คลาส:</info> {class}',
                'table' => '<info>ตาราง:</info> {table}',
                'rules_count' => '<info>จำนวนกฎ:</info> {count}',
                'scenes_count' => '<info>จำนวนฉาก:</info> {count}',
            ],
        ];
    }

    /**
     * Plain (no console tags) messages for exception messages. locale => [ key => text ]
     *
     * @return array<string, array<string, string>>
     */
    public static function getPlainMessages(): array
    {
        return [
            'zh_CN' => [
                'invalid_name_empty_plain' => '验证器类名不能为空。',
                'invalid_segment_empty_plain' => '类名段不能为空。',
                'invalid_segment_plain' => '类名段无效：{name}',
                'config_not_available' => '配置不可用。',
                'database_connection_not_provided' => '未提供数据库连接名。',
                'thinkorm_connection_not_found' => 'ThinkORM 连接不存在：{name}（可用：{available}）。',
                'database_config_invalid' => '数据库配置无效。',
                'database_connection_not_found_available' => '数据库连接不存在：{name}（可用：{available}）。',
            ],
            'zh_TW' => [
                'invalid_name_empty_plain' => '驗證器類名不能為空。',
                'invalid_segment_empty_plain' => '類名段不能為空。',
                'invalid_segment_plain' => '類名段無效：{name}',
                'config_not_available' => '配置不可用。',
                'database_connection_not_provided' => '未提供資料庫連線名稱。',
                'thinkorm_connection_not_found' => 'ThinkORM 連線不存在：{name}（可用：{available}）。',
                'database_config_invalid' => '資料庫配置無效。',
                'database_connection_not_found_available' => '資料庫連線不存在：{name}（可用：{available}）。',
            ],
            'en' => [
                'invalid_name_empty_plain' => 'Validator class name cannot be empty.',
                'invalid_segment_empty_plain' => 'Class name segment cannot be empty.',
                'invalid_segment_plain' => 'Invalid class name segment: {name}',
                'config_not_available' => 'Configuration is not available.',
                'database_connection_not_provided' => 'Database connection name not provided.',
                'thinkorm_connection_not_found' => 'ThinkORM connection not found: {name} (available: {available}).',
                'database_config_invalid' => 'Database configuration is invalid.',
                'database_connection_not_found_available' => 'Database connection not found: {name} (available: {available}).',
            ],
            'ja' => [
                'invalid_name_empty_plain' => 'バリデータークラス名を空にできません。',
                'invalid_segment_empty_plain' => 'クラス名セグメントを空にできません。',
                'invalid_segment_plain' => '無効なクラス名セグメント：{name}',
                'config_not_available' => '設定が利用できません。',
                'database_connection_not_provided' => 'データベース接続名が提供されていません。',
                'thinkorm_connection_not_found' => 'ThinkORM 接続が見つかりません：{name}（利用可能：{available}）。',
                'database_config_invalid' => 'データベース設定が無効です。',
                'database_connection_not_found_available' => 'データベース接続が見つかりません：{name}（利用可能：{available}）。',
            ],
            'ko' => [
                'invalid_name_empty_plain' => '유효성 검사 클래스 이름을 비워 둘 수 없습니다.',
                'invalid_segment_empty_plain' => '클래스 이름 세그먼트를 비워 둘 수 없습니다.',
                'invalid_segment_plain' => '잘못된 클래스 이름 세그먼트: {name}',
                'config_not_available' => '구성이 사용할 수 없습니다.',
                'database_connection_not_provided' => '데이터베이스 연결 이름이 제공되지 않았습니다.',
                'thinkorm_connection_not_found' => 'ThinkORM 연결을 찾을 수 없습니다: {name} (사용 가능: {available}).',
                'database_config_invalid' => '데이터베이스 구성이 유효하지 않습니다.',
                'database_connection_not_found_available' => '데이터베이스 연결을 찾을 수 없습니다: {name} (사용 가능: {available}).',
            ],
            'fr' => [
                'invalid_name_empty_plain' => 'Le nom de la classe de validation ne peut pas être vide.',
                'invalid_segment_empty_plain' => 'Le segment du nom de classe ne peut pas être vide.',
                'invalid_segment_plain' => 'Segment de nom de classe invalide : {name}',
                'config_not_available' => 'La configuration n\'est pas disponible.',
                'database_connection_not_provided' => 'Nom de connexion à la base de données non fourni.',
                'thinkorm_connection_not_found' => 'Connexion ThinkORM introuvable : {name} (disponible : {available}).',
                'database_config_invalid' => 'La configuration de la base de données est invalide.',
                'database_connection_not_found_available' => 'Connexion à la base de données introuvable : {name} (disponible : {available}).',
            ],
            'de' => [
                'invalid_name_empty_plain' => 'Der Name der Validierungsklasse darf nicht leer sein.',
                'invalid_segment_empty_plain' => 'Klassennamensegment darf nicht leer sein.',
                'invalid_segment_plain' => 'Ungültiges Klassennamensegment: {name}',
                'config_not_available' => 'Konfiguration ist nicht verfügbar.',
                'database_connection_not_provided' => 'Datenbankverbindungsname nicht angegeben.',
                'thinkorm_connection_not_found' => 'ThinkORM-Verbindung nicht gefunden: {name} (verfügbar: {available}).',
                'database_config_invalid' => 'Datenbankkonfiguration ist ungültig.',
                'database_connection_not_found_available' => 'Datenbankverbindung nicht gefunden: {name} (verfügbar: {available}).',
            ],
            'es' => [
                'invalid_name_empty_plain' => 'El nombre de la clase de validación no puede estar vacío.',
                'invalid_segment_empty_plain' => 'El segmento del nombre de clase no puede estar vacío.',
                'invalid_segment_plain' => 'Segmento de nombre de clase no válido: {name}',
                'config_not_available' => 'La configuración no está disponible.',
                'database_connection_not_provided' => 'Nombre de conexión a la base de datos no proporcionado.',
                'thinkorm_connection_not_found' => 'Conexión ThinkORM no encontrada: {name} (disponible: {available}).',
                'database_config_invalid' => 'La configuración de la base de datos es inválida.',
                'database_connection_not_found_available' => 'Conexión a la base de datos no encontrada: {name} (disponible: {available}).',
            ],
            'pt_BR' => [
                'invalid_name_empty_plain' => 'O nome da classe de validação não pode estar vazio.',
                'invalid_segment_empty_plain' => 'O segmento do nome da classe não pode estar vazio.',
                'invalid_segment_plain' => 'Segmento de nome de classe inválido: {name}',
                'config_not_available' => 'A configuração não está disponível.',
                'database_connection_not_provided' => 'Nome da conexão do banco de dados não fornecido.',
                'thinkorm_connection_not_found' => 'Conexão ThinkORM não encontrada: {name} (disponível: {available}).',
                'database_config_invalid' => 'A configuração do banco de dados é inválida.',
                'database_connection_not_found_available' => 'Conexão com o banco de dados não encontrada: {name} (disponível: {available}).',
            ],
            'ru' => [
                'invalid_name_empty_plain' => 'Имя класса валидации не может быть пустым.',
                'invalid_segment_empty_plain' => 'Сегмент имени класса не может быть пустым.',
                'invalid_segment_plain' => 'Недопустимый сегмент имени класса: {name}',
                'config_not_available' => 'Конфигурация недоступна.',
                'database_connection_not_provided' => 'Имя подключения к БД не указано.',
                'thinkorm_connection_not_found' => 'Подключение ThinkORM не найдено: {name} (доступно: {available}).',
                'database_config_invalid' => 'Конфигурация БД недействительна.',
                'database_connection_not_found_available' => 'Подключение к БД не найдено: {name} (доступно: {available}).',
            ],
            'vi' => [
                'invalid_name_empty_plain' => 'Tên lớp xác thực không được để trống.',
                'invalid_segment_empty_plain' => 'Đoạn tên lớp không được để trống.',
                'invalid_segment_plain' => 'Đoạn tên lớp không hợp lệ: {name}',
                'config_not_available' => 'Cấu hình không khả dụng.',
                'database_connection_not_provided' => 'Tên kết nối cơ sở dữ liệu chưa được cung cấp.',
                'thinkorm_connection_not_found' => 'Không tìm thấy kết nối ThinkORM: {name} (có sẵn: {available}).',
                'database_config_invalid' => 'Cấu hình cơ sở dữ liệu không hợp lệ.',
                'database_connection_not_found_available' => 'Không tìm thấy kết nối cơ sở dữ liệu: {name} (có sẵn: {available}).',
            ],
            'tr' => [
                'invalid_name_empty_plain' => 'Doğrulama sınıf adı boş bırakılamaz.',
                'invalid_segment_empty_plain' => 'Sınıf adı segmenti boş bırakılamaz.',
                'invalid_segment_plain' => 'Geçersiz sınıf adı segmenti: {name}',
                'config_not_available' => 'Yapılandırma kullanılamıyor.',
                'database_connection_not_provided' => 'Veritabanı bağlantı adı sağlanmadı.',
                'thinkorm_connection_not_found' => 'ThinkORM bağlantısı bulunamadı: {name} (mevcut: {available}).',
                'database_config_invalid' => 'Veritabanı yapılandırması geçersiz.',
                'database_connection_not_found_available' => 'Veritabanı bağlantısı bulunamadı: {name} (mevcut: {available}).',
            ],
            'id' => [
                'invalid_name_empty_plain' => 'Nama kelas validasi tidak boleh kosong.',
                'invalid_segment_empty_plain' => 'Segmen nama kelas tidak boleh kosong.',
                'invalid_segment_plain' => 'Segmen nama kelas tidak valid: {name}',
                'config_not_available' => 'Konfigurasi tidak tersedia.',
                'database_connection_not_provided' => 'Nama koneksi database tidak diberikan.',
                'thinkorm_connection_not_found' => 'Koneksi ThinkORM tidak ditemukan: {name} (tersedia: {available}).',
                'database_config_invalid' => 'Konfigurasi database tidak valid.',
                'database_connection_not_found_available' => 'Koneksi database tidak ditemukan: {name} (tersedia: {available}).',
            ],
            'th' => [
                'invalid_name_empty_plain' => 'ชื่อคลาสตรวจสอบไม่สามารถเว้นว่างได้',
                'invalid_segment_empty_plain' => 'ส่วนชื่อคลาสไม่สามารถเว้นว่างได้',
                'invalid_segment_plain' => 'ส่วนชื่อคลาสไม่ถูกต้อง: {name}',
                'config_not_available' => 'การกำหนดค่าไม่พร้อมใช้งาน',
                'database_connection_not_provided' => 'ไม่ได้ระบุชื่อการเชื่อมต่อฐานข้อมูล',
                'thinkorm_connection_not_found' => 'ไม่พบการเชื่อมต่อ ThinkORM: {name} (มีให้: {available})',
                'database_config_invalid' => 'การกำหนดค่าฐานข้อมูลไม่ถูกต้อง',
                'database_connection_not_found_available' => 'ไม่พบการเชื่อมต่อฐานข้อมูล: {name} (มีให้: {available})',
            ],
        ];
    }

    /**
     * Command help text (bilingual).
     *
     * @return array<string, string> locale => help text
     */
    public static function getHelpText(): array
    {
        return [
            'zh_CN' => <<<'EOF'
生成验证器类文件（默认在 app/validation 下）。

推荐用法：
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

说明：
  - 默认生成到 app/validation（大小写以现有目录为准）。
  - 使用 -p/--plugin 时默认生成到 plugin/<plugin>/app/validation。
  - 使用 -P/--path 时生成到指定相对目录（相对于项目根目录）。
  - 文件已存在时默认拒绝覆盖；使用 -f/--force 可强制覆盖。
  - 使用 -t/--table 可从数据库数据表推断规则；如需生成场景请同时指定 -s/--scenes（例如 crud）。
EOF,
            'zh_TW' => <<<'EOF'
產生驗證器類檔案（預設在 app/validation 下）。

推薦用法：
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

說明：
  - 預設產生到 app/validation（大小寫以現有目錄為準）。
  - 使用 -p/--plugin 時預設產生到 plugin/<plugin>/app/validation。
  - 使用 -P/--path 時產生到指定相對目錄（相對於專案根目錄）。
  - 檔案已存在時預設拒絕覆蓋；使用 -f/--force 可強制覆蓋。
  - 使用 -t/--table 可從資料庫資料表推斷規則；如需產生場景請同時指定 -s/--scenes（例如 crud）。
EOF,
            'en' => <<<'EOF'
Generate a validator class file (default under app/validation).

Recommended:
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

Notes:
  - By default, it generates under app/validation (case depends on existing directory).
  - With -p/--plugin, it generates under plugin/<plugin>/app/validation by default.
  - With -P/--path, it generates under the specified relative directory (to project root).
  - If the file already exists, it refuses to overwrite by default; use -f/--force to overwrite.
  - With -t/--table, it infers rules from a database table; to generate scenes, also provide -s/--scenes (e.g. crud).
EOF,
            'ja' => <<<'EOF'
バリデータークラスファイルを生成します（デフォルトは app/validation 以下）。

推奨用法：
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

説明：
  - デフォルトでは app/validation 以下に生成（大文字小文字は既存ディレクトリに合わせます）。
  - -p/--plugin 使用時は plugin/<plugin>/app/validation 以下に生成。
  - -P/--path 使用時は指定した相対ディレクトリ（プロジェクトルート基準）に生成。
  - ファイルが既に存在する場合はデフォルトで上書きしません。-f/--force で上書きできます。
  - -t/--table でデータベーステーブルからルールを推論。シーンも生成する場合は -s/--scenes（例：crud）を指定してください。
EOF,
            'ko' => <<<'EOF'
유효성 검사 클래스 파일을 생성합니다 (기본 위치: app/validation).

권장 사용법:
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

설명:
  - 기본적으로 app/validation 아래에 생성합니다 (대소문자는 기존 디렉터리에 맞춤).
  - -p/--plugin 사용 시 plugin/<plugin>/app/validation 아래에 생성합니다.
  - -P/--path 사용 시 지정한 상대 디렉터리(프로젝트 루트 기준)에 생성합니다.
  - 파일이 이미 있으면 기본적으로 덮어쓰지 않습니다. -f/--force로 덮어쓸 수 있습니다.
  - -t/--table로 데이터베이스 테이블에서 규칙을 추론합니다. 장면도 생성하려면 -s/--scenes(예: crud)를 함께 지정하세요.
EOF,
            'fr' => <<<'EOF'
Génère un fichier de classe de validation (par défaut sous app/validation).

Recommandé :
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

Notes :
  - Par défaut, génération sous app/validation (casse selon le répertoire existant).
  - Avec -p/--plugin, génération sous plugin/<plugin>/app/validation par défaut.
  - Avec -P/--path, génération dans le répertoire relatif indiqué (par rapport à la racine du projet).
  - Si le fichier existe déjà, refus d'écraser par défaut ; utilisez -f/--force pour écraser.
  - Avec -t/--table, déduction des règles à partir d'une table ; pour générer des scènes, indiquez aussi -s/--scenes (ex. crud).
EOF,
            'de' => <<<'EOF'
Erstellt eine Validierungsklassen-Datei (Standard: unter app/validation).

Empfohlen:
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

Hinweise:
  - Standardmäßig wird unter app/validation erzeugt (Groß-/Kleinschreibung nach vorhandenem Verzeichnis).
  - Mit -p/--plugin wird standardmäßig unter plugin/<plugin>/app/validation erzeugt.
  - Mit -P/--path wird im angegebenen relativen Verzeichnis (zum Projektstamm) erzeugt.
  - Wenn die Datei bereits existiert, wird standardmäßig nicht überschrieben; mit -f/--force erzwingen.
  - Mit -t/--table werden Regeln aus einer Datenbanktabelle abgeleitet; für Szenen zusätzlich -s/--scenes angeben (z. B. crud).
EOF,
            'es' => <<<'EOF'
Genera un archivo de clase de validación (por defecto bajo app/validation).

Recomendado:
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

Notas:
  - Por defecto genera bajo app/validation (mayúsculas/minúsculas según el directorio existente).
  - Con -p/--plugin genera bajo plugin/<plugin>/app/validation por defecto.
  - Con -P/--path genera en el directorio relativo indicado (respecto a la raíz del proyecto).
  - Si el archivo ya existe, por defecto no se sobrescribe; use -f/--force para sobrescribir.
  - Con -t/--table infiere reglas desde una tabla; para generar escenas, indique también -s/--scenes (ej. crud).
EOF,
            'pt_BR' => <<<'EOF'
Gera um arquivo de classe de validação (padrão em app/validation).

Recomendado:
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

Notas:
  - Por padrão gera em app/validation (maiúsculas/minúsculas conforme o diretório existente).
  - Com -p/--plugin gera em plugin/<plugin>/app/validation por padrão.
  - Com -P/--path gera no diretório relativo informado (em relação à raiz do projeto).
  - Se o arquivo já existir, por padrão não sobrescreve; use -f/--force para sobrescrever.
  - Com -t/--table infere regras a partir de uma tabela; para gerar cenas, informe também -s/--scenes (ex.: crud).
EOF,
            'ru' => <<<'EOF'
Создаёт файл класса валидации (по умолчанию в app/validation).

Рекомендуется:
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

Примечания:
  - По умолчанию создаётся в app/validation (регистр по существующей папке).
  - С -p/--plugin по умолчанию создаётся в plugin/<plugin>/app/validation.
  - С -P/--path создаётся в указанной относительной директории (от корня проекта).
  - Если файл уже есть, по умолчанию не перезаписывается; используйте -f/--force для перезаписи.
  - С -t/--table правила выводятся из таблицы БД; для сцен укажите также -s/--scenes (напр. crud).
EOF,
            'vi' => <<<'EOF'
Tạo tệp lớp xác thực (mặc định trong app/validation).

Khuyến nghị:
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

Ghi chú:
  - Mặc định tạo trong app/validation (chữ hoa/thường theo thư mục hiện có).
  - Dùng -p/--plugin thì mặc định tạo trong plugin/<plugin>/app/validation.
  - Dùng -P/--path thì tạo vào thư mục tương đối chỉ định (so với thư mục gốc dự án).
  - Nếu tệp đã tồn tại thì mặc định không ghi đè; dùng -f/--force để ghi đè.
  - Dùng -t/--table để suy ra quy tắc từ bảng cơ sở dữ liệu; để tạo cảnh thì thêm -s/--scenes (vd: crud).
EOF,
            'tr' => <<<'EOF'
Doğrulama sınıf dosyası oluşturur (varsayılan: app/validation altında).

Önerilen:
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

Notlar:
  - Varsayılan olarak app/validation altında oluşturulur (büyük/küçük harf mevcut dizine göre).
  - -p/--plugin ile varsayılan olarak plugin/<plugin>/app/validation altında oluşturulur.
  - -P/--path ile belirtilen göreli dizinde (proje köküne göre) oluşturulur.
  - Dosya zaten varsa varsayılan olarak üzerine yazılmaz; üzerine yazmak için -f/--force kullanın.
  - -t/--table ile veritabanı tablosundan kurallar çıkarılır; sahneleri de oluşturmak için -s/--scenes (örn. crud) belirtin.
EOF,
            'id' => <<<'EOF'
Membuat berkas kelas validasi (baku: di bawah app/validation).

Disarankan:
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

Catatan:
  - Secara baku dibuat di bawah app/validation (huruf besar/kecil mengikuti direktori yang ada).
  - Dengan -p/--plugin secara baku dibuat di bawah plugin/<plugin>/app/validation.
  - Dengan -P/--path dibuat di direktori relatif yang ditentukan (terhadap root proyek).
  - Jika berkas sudah ada, secara baku tidak menimpa; gunakan -f/--force untuk menimpa.
  - Dengan -t/--table aturan disimpulkan dari tabel database; untuk membuat adegan, berikan juga -s/--scenes (mis. crud).
EOF,
            'th' => <<<'EOF'
สร้างไฟล์คลาสตรวจสอบความถูกต้อง (ค่าเริ่มต้นอยู่ใต้ app/validation)

แนะนำ:
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

หมายเหตุ:
  - ค่าเริ่มต้นจะสร้างใต้ app/validation (ตัวพิมพ์ตามโฟลเดอร์ที่มีอยู่)
  - ใช้ -p/--plugin จะสร้างใต้ plugin/<plugin>/app/validation โดยค่าเริ่มต้น
  - ใช้ -P/--path จะสร้างในโฟลเดอร์สัมพัทธ์ที่กำหนด (เทียบกับรากโปรเจกต์)
  - ถ้ามีไฟล์อยู่แล้วโดยค่าเริ่มต้นจะไม่เขียนทับ ใช้ -f/--force เพื่อเขียนทับ
  - ใช้ -t/--table จะสรุปกฎจากตารางฐานข้อมูล เพื่อสร้างฉากให้ระบุ -s/--scenes ด้วย (เช่น crud)
EOF,
        ];
    }
}
