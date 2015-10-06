<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use League\Csv\Reader;

class FixtureImportCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'fixture-import:csv';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import Fixtures from CSV';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$filename = $this->argument('filename');


		if (!$this->option('testrun')=='true') {
			$this->comment('======= Importing Fixtures from '.$filename.' =========');
		} else {
			$this->comment('====== TEST ONLY Fixture Import for '.$filename.' ====');
			$this->comment('============== NO DATA WILL BE WRITTEN ==============');
		}

		if (! ini_get("auto_detect_line_endings")) {
			ini_set("auto_detect_line_endings", '1');
		}

		$csv = Reader::createFromPath($this->argument('filename'));
		$csv->setNewline("\r\n");
		$csv->setOffset(1);
		$duplicates = '';

		// Loop through the records
		$nbInsert = $csv->each(function ($row) use ($duplicates) {
			$status_id = 1;

			// Let's just map some of these entries to more user friendly words

			if (array_key_exists('0',$row)) {
				$user_name = trim($row[0]);
			} else {
				$user_name = '';
			}

			if (array_key_exists('1',$row)) {
				$user_email = trim($row[1]);
			} else {
				$user_email = '';
			}

			if (array_key_exists('2',$row)) {
				$user_username = trim($row[2]);
			} else {
				$user_username = '';
			}

			if (array_key_exists('3',$row)) {
				$user_fixture_name = trim($row[3]);
			} else {
				$user_fixture_name = '';
			}

			if (array_key_exists('4',$row)) {
				$user_fixture_serial = trim($row[4]);
			} else {
				$user_fixture_serial = '';
			}

			if (array_key_exists('5',$row)) {
				$user_fixtured_to_name = trim($row[5]);
			} else {
				$user_fixtured_to_name = '';
			}

			if (array_key_exists('6',$row)) {
				$user_fixtured_to_email = trim($row[6]);
			} else {
				$user_fixtured_to_email = '';
			}

			if (array_key_exists('7',$row)) {
				$user_fixture_seats = trim($row[7]);
			} else {
				$user_fixture_seats = '';
			}

			if (array_key_exists('8',$row)) {
				$user_fixture_reassignable = trim($row[8]);
				if ($user_fixture_reassignable!='') {
					if ((strtolower($user_fixture_reassignable)=='yes') || (strtolower($user_fixture_reassignable)=='true') || ($user_fixture_reassignable=='1')) {
						$user_fixture_reassignable = 1;
					}
				} else {
					$user_fixture_reassignable = 0;
				}
			} else {
				$user_fixture_reassignable = 0;
			}

			if (array_key_exists('9',$row)) {
				$user_fixture_supplier = trim($row[9]);
			} else {
				$user_fixture_supplier = '';
			}

			if (array_key_exists('10',$row)) {
				$user_fixture_maintained = trim($row[10]);

				if ($user_fixture_maintained!='') {
					if ((strtolower($user_fixture_maintained)=='yes') || (strtolower($user_fixture_maintained)=='true') || ($user_fixture_maintained=='1')) {
						$user_fixture_maintained = 1;
					}
				} else {
					$user_fixture_maintained = 0;
				}


			} else {
				$user_fixture_maintained = '';
			}

			if (array_key_exists('11',$row)) {
				$user_fixture_notes = trim($row[11]);
			} else {
				$user_fixture_notes = '';
			}

			if (array_key_exists('12',$row)) {
				if ($row[12]!='') {
					$user_fixture_purchase_date = date("Y-m-d 00:00:01", strtotime($row[12]));
				} else {
					$user_fixture_purchase_date = '';
				}
			} else {
				$user_fixture_purchase_date = 0;
			}

			// A number was given instead of a name
			if (is_numeric($user_name)) {
				$this->comment('User '.$user_name.' is not a name - assume this user already exists');
				$user_username = '';
			// No name was given

			} elseif ($user_name=='') {
				$this->comment('No user data provided - skipping user creation, just adding fixture');
				$first_name = '';
				$last_name = '';
				$user_username = '';

			} else {

					$name = explode(" ", $user_name);
					$first_name = $name[0];
					$email_last_name = '';
					$email_prefix = $first_name;

					if (!array_key_exists(1, $name)) {
						$last_name='';
						$email_last_name = $last_name;
						$email_prefix = $first_name;
					} else {
						$last_name = str_replace($first_name,'',$user_name);

						if ($this->option('email_format')=='filastname') {
							$email_last_name.=str_replace(' ','',$last_name);
							$email_prefix = $first_name[0].$email_last_name;

						} elseif ($this->option('email_format')=='firstname.lastname') {
							$email_last_name.=str_replace(' ','',$last_name);
							$email_prefix = $first_name.'.'.$email_last_name;

						} elseif ($this->option('email_format')=='firstname') {
							$email_last_name.=str_replace(' ','',$last_name);
							$email_prefix = $first_name;
						}


					}


					$user_username = $email_prefix;

					// Generate an email based on their name if no email address is given
					if ($user_email=='') {
						if ($first_name=='Unknown') {
							$status_id = 7;
						}
						$email = strtolower($email_prefix).'@'.$this->option('domain');
						$user_email = str_replace("'",'',$email);
					}
			}

			$this->comment('Full Name: '.$user_name);
			$this->comment('First Name: '.$first_name);
			$this->comment('Last Name: '.$last_name);
			$this->comment('Username: '.$user_username);
			$this->comment('Email: '.$user_email);
			$this->comment('Fixture Name: '.$user_fixture_name);
			$this->comment('Serial No: '.$user_fixture_serial);
			$this->comment('Fixtured To Name: '.$user_fixtured_to_name);
			$this->comment('Fixtured To Email: '.$user_fixtured_to_email);
			$this->comment('Seats: '.$user_fixture_seats);
			$this->comment('Reassignable: '.$user_fixture_reassignable);
			$this->comment('Supplier: '.$user_fixture_supplier);
			$this->comment('Maintained: '.$user_fixture_maintained);
			$this->comment('Notes: '.$user_fixture_notes);
			$this->comment('Purchase Date: '.$user_fixture_purchase_date);

			$this->comment('------------- Action Summary ----------------');

			if ($user_username!='') {
				if ($user = User::where('username', $user_username)->whereNotNull('username')->first()) {
					$this->comment('User '.$user_username.' already exists');
				} else {
					// Create the user
					$user = Sentry::createUser(array(
						'first_name' => $first_name,
						'last_name' => $last_name,
						'email'     => $user_email,
						'username'     => $user_username,
						'password'  => substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10),
						'activated' => true,
						'permissions' => array(
							'admin' => 0,
							'user'  => 1,
						),
						'notes'         => 'User importerd through fixture importer'
					));

					// Find the group using the group id
					$userGroup = Sentry::findGroupById(3);

					// Assign the group to the user
					$user->addGroup($userGroup);
					$this->comment('User '.$first_name.' created');
				}
			} else {
				$user = new User;
				$user->user_id = NULL;
			}


			// Check for the supplier match and create it if it doesn't exist
			if ($supplier = Supplier::where('name', $user_fixture_supplier)->first()) {
				$this->comment('Supplier '.$user_fixture_supplier.' already exists');
			} else {
				$supplier = new Supplier();
				$supplier->name = e($user_fixture_supplier);
				$supplier->user_id = 1;

				if ($supplier->save()) {
					$this->comment('Supplier '.$user_fixture_supplier.' was created');
	            } else {
					$this->comment('Something went wrong! Supplier '.$user_fixture_supplier.' was NOT created');
				}

			}


			// Add the fixture
			$fixture = new Fixture();
			$fixture->name = e($user_fixture_name);
			if ($user_fixture_purchase_date!='') {
				$fixture->purchase_date = $user_fixture_purchase_date;
			} else {
				$fixture->purchase_date = NULL;
			}
			$fixture->serial = e($user_fixture_serial);
			$fixture->seats = e($user_fixture_seats);
			$fixture->supplier_id = $supplier->id;
			$fixture->user_id = 1;
			if ($user_fixture_purchase_date!='') {
				$fixture->purchase_date = $user_fixture_purchase_date;
			} else {
				$fixture->purchase_date = NULL;
			}
			$fixture->fixture_name = $user_fixtured_to_name;
			$fixture->fixture_email = $user_fixtured_to_email;
			$fixture->notes = e($user_fixture_notes);

			if ($fixture->save()) {
				$this->comment('Fixture '.$user_fixture_name.' with serial number '.$user_fixture_serial.' was created');


				$fixture_seat_created = 0;

				for ($x = 0; $x < $user_fixture_seats; $x++) {
					// Create the fixture seat entries
					$fixture_seat = new FixtureSeat();
					$fixture_seat->fixture_id = $fixture->id;

					// Only assign the first seat to the user
					if ($x==0) {
						$fixture_seat->assigned_to = $user->id;
					} else {
						$fixture_seat->assigned_to = NULL;
					}

					if ($fixture_seat->save()) {
						$fixture_seat_created++;
					}
				}

				if ($fixture_seat_created > 0) {
					$this->comment($fixture_seat_created.' seats were created');
				} else {
					$this->comment('Something went wrong! NO seats for '.$user_fixture_name.' were created');
				}



            } else {
				$this->comment('Something went wrong! Fixture '.$user_fixture_name.' was NOT created');
			}


			$this->comment('=====================================');

			return true;

		});


	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('filename', InputArgument::REQUIRED, 'File for the CSV import.'),
		);
	}


	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('domain', null, InputOption::VALUE_REQUIRED, 'Email domain for generated email addresses.', null),
			array('email_format', null, InputOption::VALUE_REQUIRED, 'The format of the email addresses that should be generated. Options are firstname.lastname, firstname, filastname', null),
			array('testrun', null, InputOption::VALUE_REQUIRED, 'Test the output without writing to the database or not.', null),
		);
	}


}
