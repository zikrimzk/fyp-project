<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'occurred_at',
        'category',
        'event',
        'outcome',
        'actor_type',
        'actor_id',
        'actor_identifier',
        'actor_name',
        'subject_type',
        'subject_id',
        'subject_label',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'metadata' => 'array',
    ];

    /** Audit records are append-only. Retention uses a direct query in the scheduler. */
    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw new \LogicException('Audit log records cannot be modified.');
        }

        return parent::save($options);
    }

    public function delete(): ?bool
    {
        throw new \LogicException('Audit log records cannot be deleted manually.');
    }
}
