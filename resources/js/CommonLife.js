document.addEventListener('DOMContentLoaded', function () {

    /* ------------------ Fonction de notification ------------------ */
    function showNotification(message) {
        const notif = document.createElement('div');
        notif.classList.add('notif'); // Définir le style dans commonLife.css
        notif.textContent = message;
        document.body.appendChild(notif);
        setTimeout(() => {
            notif.style.opacity = '0';
            setTimeout(() => {
                notif.remove();
            }, 500);
        }, 3000);
    }

    /* ------------------ Variables DOM communes ------------------ */
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

    /* ------------------ Variables pour la modale de soumission (étudiant) ------------------ */
    const completeTaskButtons = document.querySelectorAll('.complete-task-btn');
    const submissionModal = document.getElementById('submissionModal');
    const closeSubmissionModal = document.getElementById('closeSubmissionModal');
    const submissionForm = document.getElementById('submissionForm');
    const submissionTaskIdInput = document.getElementById('submissionTaskId');
    const submissionCommentInput = document.getElementById('submissionComment');

    /* ------------------ Fonctions utilitaires ------------------ */
    function resetTaskForm() {
        taskForm.reset();
        taskIdInput.value = "";
        overrideMethodInput.value = "";
        modalTitle.textContent = "Créer une tâche";
    }

    /* ------------------ Gestion de la modale Admin (création/modification) ------------------ */
    if (openModal) {
        openModal.addEventListener('click', function () {
            resetTaskForm();
            modalForm.classList.remove('hidden');
        });
    }
    if (closeModal) {
        closeModal.addEventListener('click', () => modalForm.classList.add('hidden'));
        modalForm.addEventListener('click', e => {
            if (e.target === modalForm) modalForm.classList.add('hidden');
        });
    }

    taskForm && taskForm.addEventListener('submit', function (e) {
        e.preventDefault();
        let url, method;

        if (taskIdInput.value) { // Mode modification
            overrideMethodInput.value = "POST"; // Doit être défini AVANT la création du FormData
            url = `${window.Laravel.baseUrl}/tasks/${taskIdInput.value}`;
            method = "POST"; // Envoi en POST avec _method=PUT
        } else { // Création
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
                    // Mode modification
                    const taskCard = document.querySelector(`.task-card-cyan[data-id="${data.id}"]`);
                    if (taskCard) {
                        taskCard.querySelector('h3').textContent = data.title;
                        taskCard.querySelector('p').textContent = data.description;
                        taskCard.setAttribute('data-title', data.title);
                        taskCard.setAttribute('data-description', data.description);
                    }
                    showNotification("Tâche modifiée avec succès");
                } else {
                    // Mode création
                    const taskCard = document.createElement('div');
                    taskCard.classList.add('task-card-cyan');
                    taskCard.setAttribute('data-id', data.id);
                    taskCard.setAttribute('data-title', data.title);
                    taskCard.setAttribute('data-description', data.description);
                    if (window.Laravel.isAdmin === true) {
                        taskCard.classList.add('draggable-task');
                        taskCard.setAttribute('draggable', 'true');
                        const deleteBtn = document.createElement('button');
                        deleteBtn.classList.add('delete-btn');
                        deleteBtn.textContent = '×';
                        taskCard.appendChild(deleteBtn);
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
                resetTaskForm();
                modalForm.classList.add('hidden');
            })
            .catch(error => console.error("Erreur:", error));
    });

    // Ouvrir la modale en mode édition (admin)
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

    // Suppression d'une tâche (admin)
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

    // Activation du Drag & Drop pour l'administrateur
    if (window.Laravel.isAdmin === true) {
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

    /* ------------------ Gestion de la modale de soumission (étudiant) ------------------ */
    if (completeTaskButtons) {
        completeTaskButtons.forEach(button => {
            button.addEventListener('click', function () {
                const taskCard = button.closest('.task-card-cyan');
                const taskId = taskCard.getAttribute('data-id');
                submissionTaskIdInput.value = taskId;
                submissionModal.classList.remove('hidden');
            });
        });
    }

    if (closeSubmissionModal) {
        closeSubmissionModal.addEventListener('click', () => submissionModal.classList.add('hidden'));
        submissionModal.addEventListener('click', e => {
            if (e.target === submissionModal) submissionModal.classList.add('hidden');
        });
    }

    submissionForm && submissionForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(submissionForm);
        fetch(window.Laravel.routes.taskSubmissionsStore, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                "Accept": "application/json"
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.errors) {
                    alert("Erreur de validation: " + JSON.stringify(data.errors));
                    return;
                }
                alert("Votre participation a été validée avec succès.");
                submissionModal.classList.add('hidden');
                submissionForm.reset();
                // Optionnel : Mettre à jour l'interface pour marquer la tâche comme complétée
            })
            .catch(error => console.error("Erreur:", error));
    });
});
