<div {{ $attributes->merge(['class' => $makeCardClass()]) }}>

    {{-- Card header --}}
    <div class="{{ $makeCardHeaderClass() }}">

        {{-- Title --}}
        <h3 class="{{ $makeCardTitleClass() }}">
            @isset($icon)<i class="{{ $icon }} mr-2"></i>@endisset
            @isset($title){{ $title }}@endisset
        </h3>

        {{-- Tools --}}
        <div class="card-tools">
            @isset($maximizable)
                <x-button theme="tool" data-card-widget="maximize" icon="fas fa-lg fa-expand"/>
            @endisset

            @if($collapsible === 'collapsed')
                <x-button theme="tool" data-card-widget="collapse" icon="fas fa-lg fa-plus"/>
            @elseif (isset($collapsible))
                <x-button theme="tool" data-card-widget="collapse" icon="fas fa-lg fa-minus"/>
            @endif

            @isset($removable)
                <x-button theme="tool" data-card-widget="remove" icon="fas fa-lg fa-times"/>
            @endisset
        </div>

    </div>

    {{-- Card body --}}
    @if(! $slot->isEmpty())
        <div class="card-body">{{ $slot }}</div>
    @endif

    {{-- Card overlay --}}
    @if($disabled)
        <div class="overlay">
            <i class="fas fa-2x fa-ban text-gray"></i>
        </div>
    @endif

</div>