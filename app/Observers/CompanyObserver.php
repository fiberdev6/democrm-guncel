<?php

namespace App\Observers;

use App\Models\ServiceStage;
use App\Models\Tenant;

class CompanyObserver
{
    
    /**
     * Handle the Tenant "created" event.
     */
    public function created(Tenant $tenant): void
    {
        $defaultStages = ServiceStage::whereNull('firma_id')->get();

        foreach ($defaultStages as $stage) {
            ServiceStage::create([
                'asama' => $stage->asama,
                'altAsamalar' => $stage->altAsamalar,
                'sira' => $stage->sira,
                'ilkServis' => $stage->ilkServis,
                'sonServis' => $stage->sonServis,
                'firma_id' => $tenant->id, // Yeni firmaya bağla
            ]);
        }
    }

    /**
     * Handle the Tenant "updated" event.
     */
    public function updated(Tenant $tenant): void
    {
        //
    }

    /**
     * Handle the Tenant "deleted" event.
     */
    public function deleted(Tenant $tenant): void
    {
        //
    }

    /**
     * Handle the Tenant "restored" event.
     */
    public function restored(Tenant $tenant): void
    {
        //
    }

    /**
     * Handle the Tenant "force deleted" event.
     */
    public function forceDeleted(Tenant $tenant): void
    {
        //
    }


}
