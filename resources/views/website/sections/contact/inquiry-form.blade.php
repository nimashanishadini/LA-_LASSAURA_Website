<section id="inquiry-form" class="scroll-mt-8 border border-brand-border bg-white/40 p-6 sm:p-8">
    <p class="section-label">We would love to hear from you</p>
    <h2 class="mt-3 font-heading text-3xl sm:text-4xl">Send an inquiry</h2>
    <p class="body-copy mt-3">Tell us a little about your plans. Fields marked * are required.</p>
    @if(session('inquiry_status'))<p role="status" class="mt-5 border border-emerald-700/30 bg-emerald-50 p-4 text-sm text-emerald-900">{{ session('inquiry_status') }}</p>@endif
    @if($errors->any())<div role="alert" class="mt-5 border border-red-300 bg-red-50 p-4 text-sm text-red-800">Please check the highlighted fields below.</div>@endif
    <form method="POST" action="{{ route('contact.store') }}" class="mt-6 space-y-5">
        @csrf
        @foreach(['name'=>['Full name','text','120','name'], 'email'=>['Email address','email','255','email'], 'phone'=>['Phone number (optional)','tel','30','tel'], 'subject'=>['Subject','text','160','off']] as $field=>$options)
            <div>
                <label for="inquiry-{{ $field }}" class="block text-sm font-medium">{{ $options[0] }}{{ $field !== 'phone' ? ' *' : '' }}</label>
                <input id="inquiry-{{ $field }}" name="{{ $field }}" type="{{ $options[1] }}" maxlength="{{ $options[2] }}" autocomplete="{{ $options[3] }}" value="{{ old($field) }}" @required($field !== 'phone') @if($errors->has($field)) aria-invalid="true" aria-describedby="inquiry-{{ $field }}-error" @endif class="mt-2 block w-full border-brand-border bg-transparent px-4 py-3 text-sm text-navy focus:border-gold focus:ring-gold">
                @error($field)<p id="inquiry-{{ $field }}-error" class="mt-2 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>
        @endforeach
        <div><label for="inquiry-message" class="block text-sm font-medium">Your message *</label><textarea id="inquiry-message" name="message" rows="5" minlength="10" maxlength="5000" required @if($errors->has('message')) aria-invalid="true" aria-describedby="inquiry-message-error" @endif class="mt-2 block w-full border-brand-border bg-transparent px-4 py-3 text-sm text-navy focus:border-gold focus:ring-gold">{{ old('message') }}</textarea>@error('message')<p id="inquiry-message-error" class="mt-2 text-xs text-red-700">{{ $message }}</p>@enderror</div>
        <p class="text-xs leading-relaxed text-[#42566e]">Your contact details will be used to respond to this inquiry. Sending an inquiry does not reserve a room.</p>
        <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center gap-5 bg-gold px-6 py-3 text-sm font-medium transition hover:bg-dark-gold hover:text-white">Send inquiry <span aria-hidden="true">→</span></button>
    </form>
</section>
