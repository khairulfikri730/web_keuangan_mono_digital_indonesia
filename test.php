<?php echo json_encode(\App\Models\Transaction::select('id', 'total', 'created_at')->orderBy('id', 'desc')->take(5)->get());
