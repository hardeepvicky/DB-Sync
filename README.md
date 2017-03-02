# DB-Sync : Mysql Sync Tool
<p>
This tool use to sync database among developers working in git version control project. In this each git branch have seprate changes logs written in local drive. To distribute the change, Developer have to write database changes to then push them to git
<h3>
<b>Important Note : </b> I tested with Mysql Workbench. it is strongly recommended that use Mysql Workbench. Beacuse it log all its queries to mysql.general_log table correctly. Other tool like phpmyadmin do not log query correctly.</h3>
</p>
# Setup
<p>
<ul>
<li>Download this tool, put inside of your working project</li>
<li>Rename config.sample.php to config.php</li>
<li>Change Constant <b>BASE_URL</b>(url path of db sync), <b>DEVELOPER</b>(Developer Name), <b>Git Path</b>git_version("[PATH]") [PATH] is path of your project where .git is located </li>
<li>Change static database config</li>
<li><b>Mysql Setting - Run Following Queries</b>
  <ul>
    <li>SET GLOBAL log_output = 'TABLE';</li>
    <li>SET GLOBAL general_log = 'ON';</li>
  </ul>
  </li>
</ul>
</p>
# Options 
<p>
<b>SharedConfig::$dml_tables</b> : By Default this tool only fetch DDL Change, But in some cases DML statements are required to distribute. So if you want to log DML change also, add those table names in this static variable
<br/>
<pre>
class SharedConfig
{
    public static $dml_tables = array(
        "settings", "menus"
    );
}
</pre>
In above Code, Tool will also log DML Changes of menus and settings table.
</p>
