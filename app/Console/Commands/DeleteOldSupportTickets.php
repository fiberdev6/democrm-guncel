<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeleteOldSupportTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature ='support:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kapanan destek taleplerini 1 ay sonra siler (ekleri dahil)';

    /**
     * Execute the console command.
     */
     public function handle()
    {
        $this->info('Eski destek talepleri temizleniyor...');
        
        // 1 aydan eski ve kapalı olan talepler
        $oneMonthAgo = Carbon::now()->subMonth();
        
        $oldTickets = SupportTicket::where('status', 'kapali')
            ->where('updated_at', '<', $oneMonthAgo)
            ->with('replies')
            ->get();
        
        if ($oldTickets->isEmpty()) {
            $this->info('Silinecek eski talep bulunamadı.');
            Log::info('DeleteOldSupportTickets: Silinecek talep yok');
            return 0;
        }
        
        $deletedCount = 0;
        $deletedFilesCount = 0;
        
        foreach ($oldTickets as $ticket) {
            try {
                $ticketNumber = $ticket->ticket_number;
                $tenantId = $ticket->tenant_id;
                
                // 1. Ticket'a ait dosyaları sil
                if ($ticket->attachments && is_array($ticket->attachments)) {
                    foreach ($ticket->attachments as $attachment) {
                        if (isset($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
                            Storage::disk('public')->delete($attachment['path']);
                            $deletedFilesCount++;
                        }
                    }
                }
                
                // 2. Reply'lere ait dosyaları sil
                foreach ($ticket->replies as $reply) {
                    if ($reply->attachments && is_array($reply->attachments)) {
                        foreach ($reply->attachments as $attachment) {
                            if (isset($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
                                Storage::disk('public')->delete($attachment['path']);
                                $deletedFilesCount++;
                            }
                        }
                    }
                }
                
                // 3. Ticket klasörünü tamamen sil (eğer varsa)
                $tenant = $ticket->tenant;
                if ($tenant && $tenant->firma_slug) {
                    $ticketFolder = "support_attachments/firma_{$tenant->firma_slug}/ticket_{$ticketNumber}";
                    if (Storage::disk('public')->exists($ticketFolder)) {
                        Storage::disk('public')->deleteDirectory($ticketFolder);
                    }
                }
                
                // 4. Reply'leri sil
                SupportTicketReply::where('support_ticket_id', $ticket->id)->delete();
                
                // 5. Ticket'ı sil
                $ticket->delete();
                
                $deletedCount++;
                $this->info("Silindi: #{$ticketNumber} (Tenant: {$tenantId})");
                
            } catch (\Exception $e) {
                $this->error("Hata: Ticket #{$ticket->ticket_number} silinirken sorun oluştu - " . $e->getMessage());
                Log::error('DeleteOldSupportTickets hata:', [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("Toplam {$deletedCount} destek talebi ve {$deletedFilesCount} dosya silindi.");
        
        Log::info('DeleteOldSupportTickets tamamlandı:', [
            'deleted_tickets' => $deletedCount,
            'deleted_files' => $deletedFilesCount
        ]);
        
        return 0;
    }
}
