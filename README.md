# What is Laravel CRM?
<p>Laravel-CRM is a slow-moving project currently in the early stages. This is a remake of my barebones Client Management System using Laravel 5.5. You can view the original <a target="_" href="https://github.com/kkamara/crm">here</a>.</p>

# Installation
<p>Ensure you have <a target="_" href="http://php.net/downloads.php">php 7.0+</a> and <a target='_' href='https://getcomposer.org/'>Composer</a> installed <a target="_" href="https://laravel.com/docs/5.5/database#configuration">database engine</a> of your choosing, preferably Mariadb or MySQL.</p>
<p>Also make sure you have the latest version of <a target="_" href="https://nodejs.org/en/">NodeJS</a> and <a target="_" href="https://www.npmjs.com/">NPM</a> installed.</p>
<p>1. Open the command line and run:</p>
<p><pre>git clone https://github.com/kkamara/laravel-crm [installation-path]</pre></p>
<p>2. Go to the folder you downloaded Laravel CRM to and run the following in the command line:</p>
<p>To generate a secure hash for the project:</p>
<p><pre>php artisan key:generate</pre></p>
<p>To update project dependencies where necessary:</p>
<p><pre>npm update;composer update</pre></p>
<p>
  <span>3. Rename the file <b>.env.example</b> to <b>.env</b> and then update the contents to link to your desired database.</span>
  <br>
  <span><a target='_' href='https://laravel.com/docs/5.5/configuration#environment-configuration'>Click here for further assisstance</a></span>
 </p>
<p>4. Run the following to establish the required data schema in your chosen database:</p>
<p><pre>php artisan migrate</pre></p>
<p>5. Now we will generate fake data:</p>
<p>Open Laravel Tinker by running:</p>
<p><pre>php artisan tinker</pre></p>
<p>And run the following</p>
<p><pre>
  /*
   * Using database/factories/UserFactory.php we will generate
   * fake records for logs, clients and users
   */
  factory('App\Log', 200)->create(); // Change 200 to your desired number
</pre></p>
<p>Then close Tinker with <code>exit</code> or <code>CTRL-Z</code>.</p>
<p>6. Setting up roles and permissions</p>
<p>Run the following in the installation folder:</p>
<p><pre>php artisan db:seed --class=PermissionsTableSeeder</pre></p>
<p><pre>php artisan db:seed --class=RolesTableSeeder</pre></p>
<p>7. Run the following to setup the server:</p>
<p><pre>php artisan serve</pre></p>
<p>You should now be able to access the project by entering <code>http://localhost:8000</code> into your web browser.</p>

# Popular APIs Included
<ul>
<li><a target="_" href="https://github.com/spatie/laravel-permission">Spatie's laravel-permissions</a></li>
  <li><a target="_" href="https://developers.google.com/gmail/api/guides/">Gmail API</a></li>
<li>Front end design by <a href="https://prepen.io/j_holtslander/pen/XmpMEp">maridlcrmn & j_holtslander</a></li>
</ul>
