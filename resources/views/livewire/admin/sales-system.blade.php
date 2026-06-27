<div class="container py-4">
    {{-- Main Panel --}}
    <div class="card premium-card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="bg-black text-white p-4 d-flex align-items-center justify-content-between border-bottom border-gold border-opacity-25">
            <div class="d-flex align-items-center gap-3">
                <div class="header-icon bg-gold text-black rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-link-45deg fs-4 text-dark"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-uppercase tracking-wider" style="color: white; letter-spacing: 1px;">Customer Order Link Generator</h5>
                    <p class="mb-0 text-muted small">Choose a customer and select a target validity date to generate a unique ordering portal link</p>
                </div>
            </div>
            <span class="badge border border-gold px-3 py-2 rounded-pill small">
                <i class="bi bi-calendar3 me-1"></i> {{ date('D, M d Y') }}
            </span>
        </div>

        <div class="card-body p-4 bg-white">
            <div class="row g-4 justify-content-center">
                <div class="col-md-8">
                    {{-- Form --}}
                    <div class="p-4 border rounded-3 bg-light mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small"><i class="bi bi-person-fill me-1 text-primary"></i>1. Select Customer</label>
                                <select class="form-select border-gold fw-bold text-dark" wire:model.live="customerId">
                                    <option value="">-- Choose Customer --</option>
                                    @foreach($customers as $cust)
                                        <option value="{{ $cust->id }}">{{ $cust->name }} ({{ $cust->phone ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                                @error('customerId') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small"><i class="bi bi-calendar-event me-1 text-primary"></i>2. Validity Limit Date</label>
                                <input type="date" class="form-control border-gold fw-bold text-dark" wire:model.live="validUntil">
                                @error('validUntil') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        @if($selectedCustomer)
                            <div class="mt-4 p-3 border rounded bg-white" style="font-size: 0.85rem;">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle me-1 text-primary"></i>Customer Selected Details</h6>
                                <div class="row">
                                    <div class="col-sm-6 mb-1">
                                        <strong class="text-muted">Name:</strong> <span class="text-dark fw-bold">{{ $selectedCustomer->name }}</span>
                                    </div>
                                    <div class="col-sm-6 mb-1">
                                        <strong class="text-muted">Phone:</strong> <span class="text-dark">{{ $selectedCustomer->phone ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-12">
                                        <strong class="text-muted">Address:</strong> <span class="text-dark">{{ $selectedCustomer->address ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="text-center mb-4">
                        <button class="btn btn-gold-premium btn-lg px-5 py-3 rounded-pill text-white fw-bold d-inline-flex align-items-center gap-2"
                            wire:click="generateLink" {{ !$customerId ? 'disabled' : '' }}>
                            <i class="bi bi-lightning-fill"></i>
                            <span>GENERATE ORDERING LINK</span>
                        </button>
                    </div>

                    {{-- Generated Link display --}}
                    @if($generatedLink)
                        <div class="p-4 border border-gold rounded-3 bg-light-gold shadow-sm animate-fade-in">
                            <h6 class="fw-bold text-gold text-uppercase small mb-2"><i class="bi bi-check-circle-fill me-1"></i>Generated Shareable Link</h6>
                            <div class="input-group">
                                <input type="text" class="form-control fw-bold border-gold bg-white text-dark select-all" 
                                    id="generatedLinkInput" value="{{ $generatedLink }}" readonly>
                                <button class="btn btn-dark fw-bold border-gold text-gold" type="button" onclick="copyLinkToClipboard()">
                                    <i class="bi bi-clipboard me-1"></i> Copy Link
                                </button>
                            </div>
                            <small class="text-muted mt-2 d-block">You can copy and send this link to the customer via WhatsApp, SMS, or Email.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function copyLinkToClipboard() {
            const copyText = document.getElementById("generatedLinkInput");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);

            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Link copied to clipboard.',
                timer: 1500,
                showConfirmButton: false
            });
        }
    </script>
    @endpush

    @push('styles')
    <style>
        .premium-card {
            box-shadow: 0 15px 40px rgba(0,0,0,0.1) !important;
        }
        .header-icon {
            background: #c5a02c !important;
            box-shadow: 0 4px 10px rgba(197, 160, 44, 0.4);
        }
        .border-gold {
            border-color: #c5a02c !important;
        }
        .bg-light-gold {
            background-color: #fffdf6;
        }
        .text-gold {
            color: #c5a02c !important;
        }
        .btn-gold-premium {
            background: linear-gradient(135deg, #161b97 0%, #0d0f5e 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(22, 27, 151, 0.3);
            transition: all 0.3s;
        }
        .btn-gold-premium:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(22, 27, 151, 0.5);
        }
        .select-all {
            user-select: all;
        }
    </style>
    @endpush
</div>