<?php

namespace Sofa\ModelLocking;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;

class FlushExpiredLocks extends Command
{
    /** @var string */
    protected $signature = 'locks:flush';

    /** @var string */
    protected $description = 'Flush all expired model locks';

    /**
     * Runs the command:
     *  1. gets all expired locks
     *  2. deletes them
     *  3. fires ModelUnlocked broadcasting event for each
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     * @return void
     */
    public function handle(Dispatcher $events)
    {
        DB::connection('product')->beginTransaction();
		$unlocked = ModelLock::expired()->lockForUpdate()->with('model')->get()->pluck('model')->toBase()->unique()->filter();

        ModelLock::expired()->delete();

        foreach ($unlocked as $model) {
            $events->dispatch(new ModelUnlocked($model));
        }
		DB::connection('product')->commit();
    }
}
