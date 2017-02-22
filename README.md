# DB-Sync : Mysql Sync Tool
<p>
This tool use to sync database among developers working in git version control project. In this each git branch have seprate changes logs written in local drive. To distribute the change, Developer have to write database changes to then push them to git
</p>
# Setup
<p>
<ul>
<li>Download this tool, put inside of your working project</li>
<li>Rename config.sample.php to config.php</li>
<li>Change Constant <b>BASE_URL</b>(url path of db sync), <b>DEVELOPER</b>(Developer Name), <b>GIT_VERSION</b>(Current Git Branch)</li>
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
<b>config::$dml_tables</b> : By Default this tool only fetch DDL Change, But in some cases DML statements are required to distribute. So if you want to log DML change also, add those table names in this static variable
<br/>
<pre>
class config
{
    public static $dml_tables = array(
        "users", "settings"
    );
}
</pre>
In above Code, Tool will also log DML Changes of users and settings table.
</p>
