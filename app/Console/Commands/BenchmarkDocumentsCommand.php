<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;

class BenchmarkDocumentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:benchmark-documents
        {--iterations=30 : Number of iterations}
        {--status= : Document status filter}
        {--category_id= : Category UUID filter}
        {--search= : Search keyword for title}
        {--trash : Benchmark trash query (onlyTrashed)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Benchmark document listing query used by API index endpoint';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $iterations = max((int) $this->option('iterations'), 1);
        $status = $this->option('status');
        $categoryId = $this->option('category_id');
        $search = $this->option('search');
        $trash = (bool) $this->option('trash');

        $this->info('Running document query benchmark...');
        $this->line('Iterations: ' . $iterations);

        $times = [];

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);

            $query = $trash
                ? Document::onlyTrashed()->with(['category', 'currentVersion'])
                : Document::query()->with(['category', 'currentVersion']);

            if (!$trash && $status) {
                $query->where('status', $status);
            }

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            if ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            }

            $query->orderByDesc('updated_at')->paginate(10)->items();

            $times[] = (microtime(true) - $start) * 1000;
        }

        sort($times);

        $avg = array_sum($times) / count($times);
        $min = $times[0];
        $max = $times[count($times) - 1];
        $p95 = $times[(int) floor((count($times) - 1) * 0.95)];

        $this->table(['metric', 'ms'], [
            ['avg', number_format($avg, 2)],
            ['min', number_format($min, 2)],
            ['max', number_format($max, 2)],
            ['p95', number_format($p95, 2)],
        ]);

        return self::SUCCESS;
    }
}
