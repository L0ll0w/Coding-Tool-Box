document.addEventListener('DOMContentLoaded', function () {

    /* ---------- Fonction de notification ---------- */
    function showNotification(message) {
        const notif = document.createElement('div');
        notif.classList.add('notif');  // À styliser dans commonLife.css
        notif.textContent = message;
        document.body.appendChild(notif);
        setTimeout(() => {
            notif.style.opacity = '0';
            setTimeout(() => {
                notif.remove();
            }, 500);
        }, 3000);
    }

    /* ---------- Références aux éléments DOM ---------- */
    const openModal = document.getElementById('openModal');
    const closeModal = document.getElementById('closeModal');
    const modalForm = document.getElementById('modalForm');
    const taskForm = document.getElementById('taskForm');
    const tasksContainer = document.getElementById('tasksContainer');
    const modalTitle = document.getElementById('modalTitle');
    const taskIdInput = document.getElementById('taskId');
    const overrideMethodInput = document.getElementById('overrideMethod');
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');

    /* ---------- Fonction pour réinitialiser le formulaire ---------- */
    function resetForm() {
        taskForm.reset();
        taskIdInput.value = "";
        overrideMethodInput.value = "";
        modalTitle.textContent = "Créer une tâche";
    }

    /* ---------- Gestion de la modale ---------- */
    openModal && openModal.addEventListener('click', () => {
        resetForm();
        modalForm.classList.remove('hidden');
    });

    closeModal && closeModal.addEventListener('click', () => {
        modalForm.classList.add('hidden');
    });
    modalForm && modalForm.addEventListener('click', function (e) {
        if (e.target === modalForm) {
            modalForm.classList.add('hidden');
        }
    });

    /* ---------- Soumission du formulaire (création/modification) ---------- */
    taskForm && taskForm.addEventListener('submit', function (e) {
        e.preventDefault();
        // Si en mode modification, on remplit le champ _method avant de créer le FormData
        let url, method;
        if (taskIdInput.value) {
            overrideMethodInput.value = "POST";
            url = `${window.Laravel.baseUrl}/tasks/${taskIdInput.value}`;
            method = "POST";  // On envoie en POST avec _method qui vaut PUT
        } else {
            url = window.Laravel.routes.tasksStore;
            method = "POST";
        }
        const formData = new FormData(taskForm);

        fetch(url, {
            method: method,
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                "Accept": "application/json"
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.errors) {
                    console.error("Validation errors:", data.errors);
                    alert("Erreur de validation : " + JSON.stringify(data.errors));
                    return;
                }
                if (taskIdInput.value) {
                    // Mise à jour de la carte existante en mode édition
                    const taskCard = document.querySelector(`.task-card-cyan[data-id="${data.id}"]`);
                    if (taskCard) {
                        taskCard.querySelector('h3').textContent = data.title;
                        taskCard.querySelector('p').textContent = data.description;
                        taskCard.setAttribute('data-title', data.title);
                        taskCard.setAttribute('data-description', data.description);
                    }
                    showNotification("Tâche modifiée avec succès");
                } else {
                    // Création d'une nouvelle carte
                    const taskCard = document.createElement('div');
                    taskCard.classList.add('task-card-cyan');
                    taskCard.setAttribute('data-id', data.id);
                    taskCard.setAttribute('data-title', data.title);
                    taskCard.setAttribute('data-description', data.description);
                    if (window.Laravel.isAdmin) {
                        taskCard.classList.add('draggable-task');
                        taskCard.setAttribute('draggable', 'true');
                        // Bouton de suppression
                        const deleteBtn = document.createElement('button');
                        deleteBtn.classList.add('delete-btn');
                        deleteBtn.textContent = '×';
                        taskCard.appendChild(deleteBtn);
                        // Bouton de modification
                        const editBtn = document.createElement('button');
                        editBtn.classList.add('edit-btn');
                        editBtn.textContent = 'Modifier';
                        taskCard.appendChild(editBtn);
                    }
                    const titleElement = document.createElement('h3');
                    titleElement.classList.add('font-bold', 'text-lg');
                    titleElement.textContent = data.title;
                    const descriptionElement = document.createElement('p');
                    descriptionElement.textContent = data.description;
                    taskCard.appendChild(titleElement);
                    taskCard.appendChild(descriptionElement);
                    tasksContainer.appendChild(taskCard);
                }
                resetForm();
                modalForm.classList.add('hidden');
            })
            .catch(error => console.error("Erreur:", error));
    });

    /* ---------- Ouvrir la modale en mode édition ---------- */
    tasksContainer && tasksContainer.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('edit-btn')) {
            const taskCard = e.target.closest('.task-card-cyan');
            const id = taskCard.getAttribute('data-id');
            const title = taskCard.getAttribute('data-title');
            const description = taskCard.getAttribute('data-description');

            taskIdInput.value = id;
            titleInput.value = title;
            descriptionInput.value = description;
            modalTitle.textContent = "Modifier la tâche";
            modalForm.classList.remove('hidden');
        }
    });

    /* ---------- Suppression d'une tâche ---------- */
    tasksContainer && tasksContainer.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('delete-btn')) {
            if (confirm("Voulez-vous vraiment supprimer cette tâche ?")) {
                const taskCard = e.target.closest('.task-card-cyan');
                const taskId = taskCard.getAttribute('data-id');

                fetch(`${window.Laravel.baseUrl}/tasks/${taskId}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                        "Accept": "application/json"
                    },
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            taskCard.remove();
                        } else {
                            alert("Une erreur est survenue lors de la suppression.");
                        }
                    })
                    .catch(error => console.error("Erreur :", error));
            }
        }
    });

    /* ---------- Activation du Drag & Drop pour l'administrateur ---------- */
    if (window.Laravel.isAdmin) {
        const draggableCards = document.querySelectorAll('.draggable-task');
        draggableCards.forEach(card => card.setAttribute('draggable', 'true'));
        let draggedElement = null;
        tasksContainer.addEventListener('dragstart', function (e) {
            if (e.target && e.target.classList.contains('draggable-task')) {
                draggedElement = e.target;
                e.target.style.opacity = 0.5;
            }
        });
        tasksContainer.addEventListener('dragend', function (e) {
            if (e.target && e.target.classList.contains('draggable-task')) {
                e.target.style.opacity = "";
            }
        });
        tasksContainer.addEventListener('dragover', function (e) {
            e.preventDefault();
        });
        tasksContainer.addEventListener('dragenter', function (e) {
            if (e.target && e.target.classList.contains('draggable-task')) {
                e.target.classList.add('drag-over');
            }
        });
        tasksContainer.addEventListener('dragleave', function (e) {
            if (e.target && e.target.classList.contains('draggable-task')) {
                e.target.classList.remove('drag-over');
            }
        });
        tasksContainer.addEventListener('drop', function (e) {
            e.preventDefault();
            if (e.target && e.target.classList.contains('draggable-task')) {
                e.target.classList.remove('drag-over');
                tasksContainer.insertBefore(draggedElement, e.target.nextSibling);
            }
        });
    }
});
