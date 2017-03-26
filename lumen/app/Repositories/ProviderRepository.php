<?php

namespace App\Repositories;

use App\Provider;

class ProviderRepository
{
    /**
     * Get all of the valid providers
     *
     * @return Collection
     */
    public function datatableAllEditable()
    {
        return Provider::select('id as DT_RowId', 'provider_name', 'provider_image', 'provider_url', 'provider_includes')
            ->where('id', '>', config('app.provider_lunchprovided_id'))
            ->orderBy('provider_name', 'asc');
    }

    public function providerForDate($provide_date)
    {
        return Provider::join('los_lunchdates as ld', 'ld.provider_id', '=', 'los_providers.id')
            ->where('provide_date', $provide_date)
            ->first();
    }

}
