<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Cert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;




class AssignRecordsToUser extends Command
{
    protected $signature = 'records:assign {user_id}';

    protected $description = 'Assign 100 unassigned records to a specific user';

    public function handle()
    {
        $userId = $this->argument('user_id');

        // Check if the user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID $userId not found.");
            return 1;
        }

        // Count unassigned records before update
        $unassignedCount = EmployeePP::whereNull('user_id')->count();
        $this->info("Unassigned records before update: $unassignedCount");

        DB::beginTransaction();

        try {
            $affectedRows = EmployeePP::whereNull('user_id')
                ->limit(100)
                ->update(['user_id' => $userId]);

            DB::commit();

            // Count unassigned records after update
            $unassignedCountAfter = EmployeePP::whereNull('user_id')->count();
            $this->info("Unassigned records after update: $unassignedCountAfter");

            $this->info("Successfully assigned $affectedRows records to user $userId.");
            Log::info("Assigned $affectedRows records to user $userId");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("An error occurred: " . $e->getMessage());
            Log::error("Error assigning records: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}