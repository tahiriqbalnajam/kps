<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #e2e8f0; padding: 2rem; }
        .container { max-width: 1100px; margin: 0 auto; }
        h1 { font-size: 1.5rem; color: #38bdf8; margin-bottom: 0.25rem; }
        .subtitle { color: #64748b; font-size: 0.85rem; margin-bottom: 2rem; }
        .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .logout { color: #f87171; font-size: 0.85rem; text-decoration: none; padding: 0.4rem 1rem; border: 1px solid #7f1d1d; border-radius: 6px; }
        .logout:hover { background: #7f1d1d22; }
        .card { background: #1e293b; border-radius: 12px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.25); }
        .card h2 { font-size: 1.1rem; color: #94a3b8; margin-bottom: 1rem; }
        textarea { width: 100%; padding: 1rem; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: #e2e8f0; font-family: 'SF Mono', 'Fira Code', monospace; font-size: 0.9rem; resize: vertical; min-height: 100px; }
        textarea:focus { outline: none; border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56,189,248,0.15); }
        .btn-row { display: flex; gap: 1rem; margin-top: 1rem; }
        .btn { padding: 0.65rem 1.5rem; border-radius: 8px; border: none; font-size: 0.9rem; font-weight: 600; cursor: pointer; }
        .btn-execute { background: #dc2626; color: #fff; }
        .btn-execute:hover { background: #b91c1c; }
        .btn-dryrun { background: transparent; border: 1px solid #334155; color: #94a3b8; }
        .btn-dryrun:hover { background: #1e293b; border-color: #475569; }
        .warning { background: #451a03; color: #fdba74; padding: 0.75rem 1rem; border-radius: 8px; margin-top: 1rem; font-size: 0.85rem; }

        /* Results */
        .results { margin-top: 1.5rem; }
        .results h3 { margin-bottom: 0.75rem; color: #94a3b8; font-size: 0.95rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.6rem 0.75rem; text-align: left; border-bottom: 1px solid #1e293b; font-size: 0.85rem; }
        th { color: #64748b; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; }
        tr:hover td { background: rgba(30,41,59,0.5); }
        .badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #064e3b; color: #6ee7b7; }
        .badge-error { background: #7f1d1d; color: #fca5a5; }
        .badge-dry { background: #1e3a5f; color: #93c5fd; }

        .counts { font-size: 0.85rem; color: #64748b; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-row">
            <div>
                <h1>Super Admin</h1>
                <p class="subtitle">Run queries across all tenant databases</p>
            </div>
            <a href="{{ route('super.logout') }}" class="logout" onclick="return confirm('Leave super admin?')">Logout</a>
        </div>

        <div class="card">
            <h2>SQL Query</h2>
            <form method="POST" action="{{ route('super.run') }}">
                @csrf
                <textarea name="query" placeholder="e.g. ALTER TABLE students ADD COLUMN new_field VARCHAR(191) NULL;" required>{{ $query ?? '' }}</textarea>
                <div class="btn-row">
                    <button type="submit" name="dry_run" value="1" class="btn btn-dryrun">Dry Run (no execution)</button>
                    <button type="submit" class="btn btn-execute" onclick="return confirm('This will execute the query on ALL tenant databases. Continue?')">Execute on All Databases</button>
                </div>
            </form>
            <div class="warning">
                <strong>Warning:</strong> Queries execute directly against each tenant database. Test in staging first.
            </div>
        </div>

        @if (isset($results))
            <div class="card">
                <div class="results">
                    <h3>Results @if($dryRun ?? false) (Dry Run) @endif</h3>

                    @php
                        $success = collect($results)->where('status', 'success')->count();
                        $errors  = collect($results)->where('status', 'error')->count();
                        $dry     = collect($results)->where('status', 'dry_run')->count();
                    @endphp
                    <p class="counts">{{ count($results) }} databases — {{ $success }} success, {{ $errors }} errors @if($dry > 0), {{ $dry }} dry run @endif</p>

                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Database</th>
                                <th>Status</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($results as $r)
                                <tr>
                                    <td>{{ $r['user'] }}</td>
                                    <td style="font-family: monospace; font-size: 0.8rem;">{{ $r['db'] }}</td>
                                    <td>
                                        @if ($r['status'] === 'success')
                                            <span class="badge badge-success">Success</span>
                                        @elseif ($r['status'] === 'dry_run')
                                            <span class="badge badge-dry">Dry Run</span>
                                        @else
                                            <span class="badge badge-error">Error</span>
                                        @endif
                                    </td>
                                    <td style="max-width: 300px; word-break: break-all; font-size: 0.8rem;">{{ $r['message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
