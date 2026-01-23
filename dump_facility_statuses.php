<?php
require __DIR__.'/vendor/autoload.php';
use App\Models\Facility;

foreach (Facility::all(['id','name','status']) as $f) {
    echo $f->id.':'.$f->name.':'.$f->status."\n";
}
