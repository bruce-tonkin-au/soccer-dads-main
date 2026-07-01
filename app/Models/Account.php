<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    protected $table = 'account';

    protected $primaryKey = 'accountID';

    protected $keyType = 'int';

    // LEGACY table — accountCreated / accountEdited are set manually by the
    // app (CURRENT_TIMESTAMP DB defaults), NOT Laravel created_at/updated_at.
    // Mirrors Season, not Order/Product.
    public $timestamps = false;

    protected $fillable = [
        'memberID',
        'accountValue',
        'gameID',
        'paymentID',
        'transferID',
        'accountComment',
        'accountVisible',
        'accountCreated',
        'accountEdited',
    ];

    protected $casts = [
        'accountValue'   => 'decimal:2',
        'accountVisible' => 'integer',
    ];

    // Nullable in principle, but the ledger view only shows rows with a member.
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'memberID', 'memberID');
    }
}
