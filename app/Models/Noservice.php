<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Noservice extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'noservices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        // English (optional)
        'title',
        'subtitle',
        'details',
        'result',
        // French (required)
        'titre',
        'soustitre',
        'detalserivces',
        'resultats',
        // Form wrapper keys (repeaters)
        'en',
        'fr',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'details' => 'array',
        'detalserivces' => 'array',
    ];

    /**
     * Accept a textarea (string with newlines) or array and store as JSON array.
     */
    public function setDetailsAttribute($value)
    {
        if (is_string($value)) {
            $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value)));
            $this->attributes['details'] = $lines ? json_encode(array_values($lines)) : null;
            return;
        }

        if (is_array($value)) {
            $this->attributes['details'] = json_encode(array_values($value));
            return;
        }

        $this->attributes['details'] = null;
    }

    public function setDetalserivcesAttribute($value)
    {
        if (is_string($value)) {
            $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value)));
            $this->attributes['detalserivces'] = $lines ? json_encode(array_values($lines)) : null;
            return;
        }

        if (is_array($value)) {
            $this->attributes['detalserivces'] = json_encode(array_values($value));
            return;
        }

        $this->attributes['detalserivces'] = null;
    }

    /**
     * Mutator to accept the `en` repeater data from the form and map to columns.
     * Expecting an array with a single item (because repeater minItems=1, maxItems=1).
     */
    public function setEnAttribute($value)
    {
        if (!is_array($value)) {
            return;
        }

        $item = $value[0] ?? [];

        $this->attributes['title'] = $item['title'] ?? null;
        $this->attributes['subtitle'] = $item['subtitle'] ?? null;

        if (isset($item['details']) && is_array($item['details'])) {
            $details = array_map(function ($d) {
                return $d['item'] ?? null;
            }, $item['details']);
            $details = array_values(array_filter($details, function ($v) {
                return $v !== null && $v !== '';
            }));
            $this->attributes['details'] = json_encode($details ?: null);
        } else {
            $this->attributes['details'] = json_encode(null);
        }

        $this->attributes['result'] = $item['result'] ?? null;
    }

    /**
     * Mutator to accept the `fr` repeater data from the form and map to columns.
     */
    public function setFrAttribute($value)
    {
        if (!is_array($value)) {
            return;
        }

        $item = $value[0] ?? [];

        $this->attributes['titre'] = $item['titre'] ?? null;
        $this->attributes['soustitre'] = $item['soustitre'] ?? null;

        if (isset($item['detalserivces']) && is_array($item['detalserivces'])) {
            $details = array_map(function ($d) {
                return $d['item'] ?? null;
            }, $item['detalserivces']);
            $details = array_values(array_filter($details, function ($v) {
                return $v !== null && $v !== '';
            }));
            $this->attributes['detalserivces'] = json_encode($details ?: null);
        } else {
            $this->attributes['detalserivces'] = json_encode(null);
        }

        $this->attributes['resultats'] = $item['resultats'] ?? null;
    }

    /**
     * Accessor to provide `en` structure for the form (repeater expects an array of items).
     */
    public function getEnAttribute()
    {
        $details = $this->details ?? [];
        $detailsForm = [];
        if (is_array($details)) {
            foreach ($details as $d) {
                $detailsForm[] = ['item' => $d];
            }
        }

        // Ensure at least one item to satisfy repeater minItems=1
        if (empty($detailsForm)) {
            $detailsForm[] = ['item' => null];
        }

        return [
            [
                'title' => $this->title,
                'subtitle' => $this->subtitle,
                'details' => $detailsForm,
                'result' => $this->result,
            ],
        ];
    }

    /**
     * Accessor to provide `fr` structure for the form (repeater expects an array of items).
     */
    public function getFrAttribute()
    {
        $details = $this->detalserivces ?? [];
        $detailsForm = [];
        if (is_array($details)) {
            foreach ($details as $d) {
                $detailsForm[] = ['item' => $d];
            }
        }

        if (empty($detailsForm)) {
            $detailsForm[] = ['item' => null];
        }

        return [
            [
                'titre' => $this->titre,
                'soustitre' => $this->soustitre,
                'detalserivces' => $detailsForm,
                'resultats' => $this->resultats,
            ],
        ];
    }
}
