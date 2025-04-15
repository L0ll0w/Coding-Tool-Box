<!-- resources/views/layouts/modal.blade.php -->
<div id="{{ $id }}" class="modal" data-modal="true">
    <div class="modal-content max-w-[600px] top-[20%]">
        <div class="modal-header">
            <h3 id="modalTitle" class="modal-title">
                {{ $title }}
            </h3>
            <!-- Bouton de fermeture avec un attribut de data pour faciliter la fermeture par JS -->
            <button id="closeModal" class="btn btn-xs btn-icon btn-light" data-modal-dismiss="true">
                <i class="ki-outline ki-cross"></i>
            </button>
        </div>
        <div class="modal-body">
            @yield('modal-content')
        </div>
    </div>
</div>
