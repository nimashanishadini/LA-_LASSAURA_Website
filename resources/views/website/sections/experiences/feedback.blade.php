<section id="guest-feedback" class="scroll-mt-8 border-y border-brand-border/60 bg-[#eeeee5] px-5 py-14 sm:px-8 lg:py-20">
    <div class="mx-auto max-w-[1060px]">
        <div class="mb-9 flex flex-wrap items-end justify-between gap-6">
            <div><p class="section-label">Little moments, lasting memories</p><h2 class="mt-4 font-heading text-4xl leading-tight sm:text-5xl">Stories from our guests</h2><p class="body-copy mt-4 max-w-xl">A glimpse of La Lassaura through the people who have shared their experience.</p></div>
            @if($reviewCount)<div class="text-navy"><span class="font-heading text-5xl">{{ number_format($averageRating, 1) }}</span><span class="ml-2 text-sm">/ 5</span><p class="mt-2 text-xs text-[#42566e]">Based on {{ $reviewCount }} {{ $reviewCount === 1 ? 'review' : 'reviews' }}</p></div>@endif
        </div>
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @forelse($reviews as $review)
                <article class="flex h-full flex-col border border-brand-border/60 bg-ivory p-6 sm:p-7">
                    <div class="flex items-center justify-between gap-4"><span class="text-sm tracking-[0.15em] text-dark-gold" role="img" aria-label="{{ $review->rating }} out of 5 stars">@for($star=1;$star<=5;$star++)<span aria-hidden="true">{{ $star <= $review->rating ? '★' : '☆' }}</span>@endfor</span><span class="font-heading text-4xl leading-none text-gold/60" aria-hidden="true">“</span></div>
                    <blockquote class="body-copy mt-5 flex-1 whitespace-pre-line break-words">{{ $review->comment }}</blockquote>
                    <footer class="mt-7 flex items-center gap-3 border-t border-brand-border/60 pt-5"><span aria-hidden="true" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gold/15 font-heading text-xl text-dark-gold">{{ mb_strtoupper(mb_substr($review->name,0,1)) }}</span><div><p class="text-sm font-medium">{{ $review->name }}</p><p class="mt-1 text-[11px] text-[#42566e]">{{ $review->created_at->format('F Y') }}</p></div></footer>
                </article>
            @empty
                <div class="border border-brand-border/60 bg-ivory p-8 text-center md:col-span-2 lg:col-span-3"><span class="font-heading text-5xl text-gold" aria-hidden="true">“</span><h3 class="mt-2 font-heading text-3xl">Your story could be the first</h3><p class="body-copy mt-3">We would love to hear what made your time here memorable.</p><a href="#share-feedback" class="mt-5 inline-block border-b border-gold pb-2 text-sm">Share your experience →</a></div>
            @endforelse
        </div>
        @if($reviews->hasPages())<div class="mt-7">{{ $reviews->links() }}</div>@endif
        <div id="share-feedback" class="mt-12 grid scroll-mt-8 gap-8 border-t border-brand-border pt-10 lg:grid-cols-[1fr_1.3fr] lg:gap-14">
            <div><p class="section-label">A note from you</p><h3 class="mt-4 font-heading text-3xl sm:text-4xl">How was your experience?</h3><p class="body-copy mt-4">Whether it was a quiet morning or a moment by the pool, we would love to hear your thoughts.</p><p class="mt-4 text-xs leading-relaxed text-[#42566e]">Your name, rating and feedback may appear publicly after moderation. Your email stays private. Reviews are guest-submitted and are not verified bookings.</p></div>
            <form action="{{ route('reviews.store') }}" method="POST" class="space-y-5">@csrf
                @if(session('review_status'))<p role="status" class="border border-emerald-700/30 bg-emerald-50 p-4 text-sm text-emerald-900">{{ session('review_status') }}</p>@endif
                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach(['name'=>'Your name','email'=>'Email address'] as $field=>$label)<div><label for="review-{{ $field }}" class="text-sm font-medium">{{ $label }} *</label><input id="review-{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" autocomplete="{{ $field }}" value="{{ old($field) }}" maxlength="{{ $field === 'email' ? 255 : 100 }}" required class="mt-2 block w-full border-brand-border bg-ivory px-4 py-3 text-sm focus:border-gold focus:ring-gold" @if($errors->has($field)) aria-invalid="true" aria-describedby="review-{{ $field }}-error" @endif>@error($field)<p id="review-{{ $field }}-error" class="mt-2 text-xs text-red-700">{{ $message }}</p>@enderror</div>@endforeach
                </div>
                <div><label for="review-rating" class="text-sm font-medium">Your rating *</label><select id="review-rating" name="rating" required class="mt-2 block w-full border-brand-border bg-ivory px-4 py-3 text-sm focus:border-gold focus:ring-gold"><option value="">Choose a rating</option>@foreach([5=>'5 — Excellent',4=>'4 — Very good',3=>'3 — Good',2=>'2 — Fair',1=>'1 — Poor'] as $value=>$label)<option value="{{ $value }}" @selected(old('rating') == $value)>{{ $label }}</option>@endforeach</select>@error('rating')<p class="mt-2 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div><label for="review-comment" class="text-sm font-medium">Your experience *</label><textarea id="review-comment" name="comment" rows="4" minlength="10" maxlength="2000" required class="mt-2 block w-full border-brand-border bg-ivory px-4 py-3 text-sm focus:border-gold focus:ring-gold" placeholder="Tell us about your experience…">{{ old('comment') }}</textarea>@error('comment')<p class="mt-2 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <button class="inline-flex min-h-12 items-center gap-6 bg-gold px-7 py-3 text-sm font-medium transition hover:bg-dark-gold hover:text-white">Share feedback <span aria-hidden="true">→</span></button>
            </form>
        </div>
    </div>
</section>
