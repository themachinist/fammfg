<?php
class LicenseSeatsSeeder extends Seeder
{
    public function run()
    {


        // Initialize empty array
        $fixture_seats = array();

        $date = new DateTime;


        $fixture_seats[] = array(
            'fixture_id'      	=> '1',
            'assigned_to'      	=> '1',
            'created_at' 		=> $date->modify('-10 day')->format('Y-m-d H:i:s'),
            'updated_at' 		=> $date->modify('-3 day')->format('Y-m-d H:i:s'),
            'deleted_at'  		=> NULL,
            'notes' 			=> '',
            'user_id' 			=> '1',
            'asset_id' 			=> '1',
        );

        $fixture_seats[] = array(
            'fixture_id'      	=> '1',
            'assigned_to'      	=> '1',
            'created_at' 		=> $date->modify('-10 day')->format('Y-m-d H:i:s'),
            'updated_at' 		=> $date->modify('-3 day')->format('Y-m-d H:i:s'),
            'deleted_at' 		=> NULL,
            'notes' 			=> '',
            'user_id' 			=> '1',
            'asset_id' 			=> '2',
        );

        $fixture_seats[] = array(
            'fixture_id'      	=> '1',
            'assigned_to'      	=> '1',
            'created_at' 		=> $date->modify('-10 day')->format('Y-m-d H:i:s'),
            'updated_at' 		=> $date->modify('-3 day')->format('Y-m-d H:i:s'),
            'deleted_at' 		=> NULL,
            'notes' 			=> '',
            'user_id' 			=> '1',
            'asset_id' 			=> '3',
        );

        $fixture_seats[] = array(
            'fixture_id'      	=> '1',
            'assigned_to'      	=> NULL,
            'created_at' 		=> $date->modify('-10 day')->format('Y-m-d H:i:s'),
            'updated_at' 		=> $date->modify('-3 day')->format('Y-m-d H:i:s'),
            'deleted_at' 		=> NULL,
            'notes' 			=> '',
            'user_id' 			=> '1',
            'asset_id' 			=> NULL,
        );

        $fixture_seats[] = array(
            'fixture_id'      	=> '1',
            'assigned_to'      	=> '1',
            'created_at' 		=> $date->modify('-10 day')->format('Y-m-d H:i:s'),
            'updated_at' 		=> $date->modify('-3 day')->format('Y-m-d H:i:s'),
            'deleted_at' 		=> NULL,
            'notes' 			=> '',
            'user_id' 			=> '1',
            'asset_id' 			=> NULL,
        );

        $fixture_seats[] = array(
            'fixture_id'      	=> '2',
            'assigned_to'      	=> '1',
            'created_at' 		=> $date->modify('-10 day')->format('Y-m-d H:i:s'),
            'updated_at' 		=> $date->modify('-3 day')->format('Y-m-d H:i:s'),
            'deleted_at' 		=> NULL,
            'notes' 			=> '',
            'user_id' 			=> '1',
            'asset_id' 			=> NULL,
        );


        $fixture_seats[] = array(
            'fixture_id'      	=> '2',
            'assigned_to'      	=> NULL,
            'created_at' 		=> $date->modify('-10 day')->format('Y-m-d H:i:s'),
            'updated_at' 		=> $date->modify('-3 day')->format('Y-m-d H:i:s'),
            'deleted_at' 		=> NULL,
            'notes' 			=> '',
            'user_id' 			=> '1',
            'asset_id' 			=> NULL,

        );

        // Delete all the old data
        DB::table('fixture_seats')->truncate();

        // Insert the new posts
        LicenseSeat::insert($fixture_seats);
    }

}
