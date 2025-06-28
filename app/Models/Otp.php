<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Import the Str facade for UUID generation
use Illuminate\Database\Eloquent\Factories\HasFactory; // Generally good to include if you might use model factories

class Otp extends Model
{
    // If you plan to use factories for testing/seeding, include HasFactory
    use HasFactory;

    // Define the primary key if it's not 'id' or if its type is not integer.
    // For UUIDs, 'id' is still the convention, but its type is 'string'.
    protected $primaryKey = 'id';

    // Indicate that the primary key is not an auto-incrementing integer.
    // This is crucial for UUIDs.
    public $incrementing = false;

    // Define the type of the primary key. For UUIDs, it's a string.
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',           // Include 'id' because we are explicitly setting it with Str::uuid()
        'email',
        'otp_code',
        'expires_at',
    ];

    /**
     * The attributes that should be cast to native types.
     * This ensures 'expires_at' is treated as a Carbon instance.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * The "booting" method of the model.
     * This method is called once when the model is loaded.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Listen for the 'creating' event on this model.
        // If the 'id' is not set, automatically generate a UUID for it.
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid();
            }
        });
    }
}
