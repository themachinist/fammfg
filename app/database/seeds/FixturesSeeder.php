<?php
class FixturesSeeder extends Seeder
{
    public function run()
    {


        // Initialize empty array
        $fixture = array();

        $date = new DateTime;


        $fixture[] = array(
            'name'      		=> 'Adobe Photoshop CS6',
            'serial'      		=> 'ZOMG-WtF-BBQ-SRSLY',
            'purchase_date' 	=> '2013-10-02',
            'purchase_cost' 	=> '2435.99',
            'purchase_order' 	=> '1',
            'maintained' 		=> '0',
            'order_number'  	=> '987698576946',
            'created_at' 		=> $date->modify('-10 day'),
            'updated_at' 		=> $date->modify('-3 day'),
            'seats' 			=> 5,
            'fixture_name'		=> '',
            'fixture_email'		=> '',
            'notes' 			=> '',
            'user_id'			=> 1,
            'depreciation_id'	=> 2,
            'deleted_at' 		=> NULL,
            'depreciate' 		=> '0',
        );


        $fixture[] = array(
            'name'      		=> 'Git Tower',
            'serial'      		=> '98049890394-340485934',
            'purchase_date' 	=> '2013-10-02',
            'purchase_cost' 	=> '2435.99',
            'purchase_order' 	=> '1',
            'maintained' 	=> '1',
            'order_number'  	=> '987698576946',
            'created_at' 		=> $date->modify('-10 day'),
            'updated_at' 		=> $date->modify('-3 day'),
            'seats' 			=> 2,
            'fixture_name'		=> 'Alison Gianotto',
            'fixture_email'		=> 'snipe@snipe.net',
            'notes' 			=> '',
            'user_id'			=> 1,
            'depreciation_id'	=> 2,
            'deleted_at' 		=> NULL,
            'depreciate' 		=> '0',
        );

        // Delete all the old data
        DB::table('fixtures')->truncate();

        // Insert the new posts
        License::insert($fixture);
    }

}
