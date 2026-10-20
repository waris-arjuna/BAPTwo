<?php

namespace App\Policies;

use App\Models\Dokumen;
use App\Models\User;

class DokumenPolicy
{
    /**
     * Tentukan apakah user dapat melihat/pratinjau dokumen.
     */
    public function view(User $user, Dokumen $dokumen): bool
    {
        if (! $user->can('view-cases')) {
            return false;
        }

        if ($dokumen->perkara_id && ! $dokumen->perkara) {
            return false;
        }

        return true;
    }

    /**
     * Tentukan apakah user dapat mengunduh dokumen.
     */
    public function download(User $user, Dokumen $dokumen): bool
    {
        return $this->view($user, $dokumen);
    }
}
