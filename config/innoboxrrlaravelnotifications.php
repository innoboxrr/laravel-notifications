<?php

return [

	'user_class' => 'App\Models\User',

	'excel_view' => 'innoboxrrlaravelnotifications::excel.',

	'notification_via' => ['mail', 'database'],

	'export_disk' => 's3',
	
];