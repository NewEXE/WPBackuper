��    T      �  q   \         4  !    V  	   g     q  0   �  	   �     �  C   �     !     9     H  D   _  (   �  %   �     �       $        D     Z     s     �     �     �     �     �     �     �     �     �               7     I  (   _     �  .   �  +   �     �  >   �  $   3     X     j     �  (   �     �     �     �     �  7   �  #   $     H     P     ]     i     r     �     �     �  5   �  -   �  0     8   C  7   |  4   �  *   �               )     A     O  $   W     |     �  
   �  ^   �     !     *     7  
   ;  6   F     }     �     �    �  x  �  �       �  1   
  R   <     �  &   �  �   �  2   a  .   �  $   �  �   �  d   s  Y   �     2   =   L   Q   �   /   �   @   !  9   M!     �!  $   �!  <   �!     "     "     $"     >"     Q"  4   q"  7   �"  J   �"     )#  *   @#  K   k#     �#  R   �#  I   $     e$  c   w$  7   �$  )   %  <   =%  ,   z%  M   �%     �%     &  3   &     N&  Y   U&  6   �&     �&     �&     '     !'  %   6'  &   \'     �'  &   �'  [   �'  h   (  J   �(     �(  h   M)  T   �)  t   *     �*     �*  A   �*     �*     	+  H   +  =   g+  4   �+  
   �+  �   �+     �,     �,     �,  
   �,  S   �,     '-     /-     5-         ,   1   L                     $   +   )   <           2   J   5                M      	   F      &                 -   C          =      ?       !       E           %   A   9   7   G   >                      4           #   
   B   I   "   .          '          (   O          T          0   *   6       P      R       K                  /      ;   D      Q             N   H       :      8   3      S       @        
<a href="https://codex.wordpress.org/Editing_wp-config.php#WordPress_Upgrade_Constants">Editing wp-config.php on Codex</a><br />
<a href="https://roots.io/bedrock/docs/configuration-files/">Configuration Files (Bedrock)</a><br />
<a href="https://codex.wordpress.org/Filesystem_API">Filesystem API docs</a>
 
<p>Unable to get direct access to your file system. <b>Creating backup by cron (on schedule) will not work</b>. You need to store filesystem credentials permanently.</p>
<p>There is a way to store the credentials permanently using the <code>wp-config.php</code> file.</p>

<p>Use these options to store the FTP or SSH credentials:</p>

<ol>
<li><code>FTP_HOST</code>: The host name of the server.</li>
<li><code>FTP_USER</code>: The username to use while connecting.</li>
<li><code>FTP_PASS</code>: The password to use while connecting.</li>
<li><code>FTP_PUBKEY</code>: The path of the public key which will be used while using SSH connection.</li>
<li><code>FTP_PRIKEY</code>: The path of the private key which will be used while using SSH connection.</li>
</ol>

<p>Example:<p>
<pre class="programmlisting">
define( "FTP_HOST", "ftp.example.org" );
define( "FTP_USER", "username" );
define( "FTP_PASS", "password" );
define( "FTP_PUBKEY", "/home/username/.ssh/id_rsa.pub" );
define( "FTP_PRIKEY", "/home/username/.ssh/id_rsa" );
</pre>
 Activated Addition information links Archivation via PclZip not supported for now...  Available Available and allowed Backups are save into temporary folder. You can clean this dir now. Bedrock WP installation Clean temp dir Common WP installation Create backup now and download via browser. This may take some time. Create database backup and send by email Create files backup and send by email Cron Schedule Dir <b>%s</b> is NOT writable Dir <b>%s</b> is exists and writable Directory for backups Download database backup Download files backup E-mail has been sent. E-mail not sent: %1$s E-mail to send a backup Enable Event Every Minute Execute Execute now FS connected FS not connected Filesystem connection problem For example, %1$s Full path to log file Full path to temp directory for backups. General Howdy! Here your backup of WordPress Database. Howdy! Here your backup of WordPress files. In queue Is PHP function exec() is available and allowed for execution? Is ZipArchive PHP library available? Is debug enabled? Is debug log enabled? Is normal WP installation? Is successfully connected to filesystem? Log file path Name Next execution in No Not available and (or) not allowed. Using $wpdb instead Not available. Using PclZip instead Not set Once Monthly PHP version Schedule Select schedule... Send e-mail now Settings Settings saved. Something went wrong while archivation via ZipArchive Something went wrong while cleaning temp dir. Something went wrong while executing "mysqldump" Something went wrong while getting list files for backup Something went wrong while put sql content to temp file Something went wrong while sending email via wp_mail Something went wrong while touch temp file Status System Status Temp directory cleared. Twice Monthly Unknown WP was installed with Bedrock or no? WPBackup: your WP DB backup WPBackup: your WP files backup WPBackuper WPBackuper Filesystem connection issue. See <a href="%1$s">plugin status page</a> for details. WP_DEBUG WP_DEBUG_LOG Yes ZipArchive ZipArchive: Something went wrong while closing archive exec() false true Project-Id-Version: WPBackuper 1.0.0
POT-Creation-Date: 2018-10-15 21:33+0300
PO-Revision-Date: 2018-10-15 21:33+0300
Last-Translator: 
Language-Team: 
Language: ru_RU
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Generator: Poedit 1.8.7.1
X-Poedit-Basepath: ..
Plural-Forms: nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);
X-Poedit-KeywordsList: __;_e;esc_attr__;esc_attr_e;esc_html__;esc_html_e
X-Poedit-SearchPath-0: .
 
<a href="https://codex.wordpress.org/Editing_wp-config.php#WordPress_Upgrade_Constants">Редактирование wp-config.php (Codex)</a><br />
<a href="https://roots.io/bedrock/docs/configuration-files/">Конфигурационные файлы (Bedrock)</a><br />
<a href="https://codex.wordpress.org/Filesystem_API">Документация по Wordpress API</a>
 
<p>Невозможно получить доступ к файловой системе. <b>Создание резевных копий (в т.ч. по сron-расписанию) не будет работать</b>. Вы должны указать учётные данные для соединения .</p>
<p>Сделать это можно в файле <code>wp-config.php</code>.</p>

<p>Используйте следующие опции, чтобы сохранить учётные данные от FTP или SSH:</p>

<ol>
<li><code>FTP_HOST</code>: Имя хоста.</li>
<li><code>FTP_USER</code>: Имя пользователя.</li>
<li><code>FTP_PASS</code>: Пароль.</li>
<li><code>FTP_PUBKEY</code>: Путь к публичному ключу (в случае подключения по SSH).</li>
<li><code>FTP_PRIKEY</code>: Путь к приватному ключу (в случае подключения по SSH).</li>
</ol>

<p>Например:<p>
<pre class="programmlisting">
define( "FTP_HOST", "ftp.example.org" );
define( "FTP_USER", "username" );
define( "FTP_PASS", "password" );
define( "FTP_PUBKEY", "/home/username/.ssh/id_rsa.pub" );
define( "FTP_PRIKEY", "/home/username/.ssh/id_rsa" );
</pre>
 Активно Дополнительная информация Архивация через PclZip пока не поддерживается... Доступна Доступна и разрешена Резервные копии сохраняютя во временную папку. Вы можете очистить эту папку сейчас. Wordpress установлен через Bedroсk Очистить временную папку Обычная WP установка Создать резервную копию и скачать в браузере. Может занять некоторое время. Создать резервную копию базы данных и отправить по e-mail Создать резевную копию файлов и отправить по e-mail Cron-расписание Папка <b>%s</b> недоступна для записи Папка <b>%s</b> существует и доступна для записи Папка для резервных копий Скачать резервную копию базы даннх Скачать резервную копию файлов E-mail отправлен. E-mail не отправлен: %1$s E-mail для отправки резервных копий Включить Событие Каждую минуту Выполнить Выполнить сейчас Файловая система подключена Файловая система не поключена Проблема соединения с файловой системой Например, %1$s Полный путь к лог-файлу Полный путь к папке с резервными копиями. Основные Привет! Вот Ваша резервная копия базы данных. Привет! Вот Ваша резервная копия файлов. В очереди PHP функция exex() доступна и разрешена для использования? PHP библиотека "ZipArchive" доступна? Режим отладки включён? Запись журнала отладки включена? Обычная Wordpress установка? Имеет ли плагин доступ к файловой системе? Путь к лог-файлу Имя Следующее выполнение через: Нет Не доступна и (или) не разрешена. Используется $wpdb Недоступна. Используется PсlZip Не задано Каждый месяц Версия PHP Расписание Выберите расписание Отправить e-mail сейчас Настройки Настройки сохранены. Что-то пошло не так во время архивации через ZipArchive Произошла ошибка при очистке папки с временными файлами. Что-то пошло не так при выполнении "mysqldump" Что-то пошло не так при получении файлов для создания резервной копии Что-то пошло не так при записи SQL-строки во временный файл Что-то пошло не так при отправке e-mail через wp_mail Что-то пошло не так во время выполнения "touсh" к временному файлу. Статус Статус системы Папка с временными файлами очищена. Два раза в месяц Неизвестно Wordpress установлен с помощью Bedroсk или нет? WPBackup: резервная копия базы данных WPBackup: резервная копия файлов WPBackuper WPBackuper: ошибка подключения к файловой системе. Откройте <a href="%1$s">страницу статуса плагина</a> для подробностей. WP_DEBUG WP_DEBUG_LOG Да ZipArchive ZipArchive: что-то пошло не так при закрытии архива exeс() false true 