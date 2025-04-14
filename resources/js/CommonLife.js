document.addEventListener('DOMContentLoaded', function () {


    /* ------------------ Fonction pour mettre à jour le message "Aucune tâche à afficher" ------------------ */
    function updateNoTaskMessage() {
        const noTaskElement = document.getElementById('noTasksMessage');
        const taskCards = tasksContainer.querySelectorAll('.card');
        if (taskCards.length === 0) {
            if (!noTaskElement) {
                const noTaskDiv = document.createElement('div');
                noTaskDiv.id = 'noTasksMessage';
                noTaskDiv.className = 'col-span-full text-center text-gray-600 text-lg font-medium py-4';
                noTaskDiv.textContent = "Aucune tâche à afficher.";
                tasksContainer.appendChild(noTaskDiv);
            }
        } else {
            if (noTaskElement) {
                noTaskElement.remove();
            }
        }
    }

    /* ------------------ Variables DOM - Partie Admin ------------------ */
    // Pour le modal de création/modification, on utilise l'ID "create-task-modal"
    const openModal = document.getElementById('openModal');
    const closeModal = document.getElementById('closeModal');
    const modalForm = document.getElementById('create-task-modal'); // Assurez-vous que ce modal a l'ID "create-task-modal"
    const taskForm = document.getElementById('taskForm');
    const tasksContainer = document.getElementById('tasksContainer');
    const modalTitle = document.getElementById('modalTitle');
    const taskIdInput = document.getElementById('taskId');
    const overrideMethodInput = document.getElementById('overrideMethod');
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');

    /* ------------------ Variables DOM - Partie Étudiant ------------------ */
    const completeTaskButtons = document.querySelectorAll('.complete-task-btn');
    const submissionModal = document.getElementById('submission-modal');
    const closeSubmissionModal = document.getElementById('closeSubmissionModal');
    const submissionForm = document.getElementById('submissionForm');
    const submissionTaskIdInput = document.getElementById('submissionTaskId');
    // submissionCommentInput n'est pas utilisé directement dans le JS

    /* ------------------ Fonction utilitaire pour réinitialiser le formulaire Admin ------------------ */
    function resetTaskForm() {
        taskForm.reset();
        taskIdInput.value = "";
        overrideMethodInput.value = "";
        if(modalTitle) {
            modalTitle.textContent = "Créer une tâche";
        }
    }

    /* ------------------ Gestion de la modale Admin (Création/Modification) ------------------ */
    if (openModal) {
        openModal.addEventListener('click', function () {
            console.log("DEBUG: Ouverture du formulaire d'administration (création/modification) de tâche.");
            resetTaskForm();
            if (modalForm) {
                modalForm.classList.remove('hidden');
            } else {
                console.warn("DEBUG: L'élément modalForm est introuvable.");
            }
        });
    }
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            console.log("DEBUG: Fermeture du formulaire d'administration (création/modification) de tâche.");
            resetTaskForm();
            if (modalForm) {
                modalForm.classList.add('hidden');
            }else{
                console.warn("DEBUG: L'élément modalForm est introuvable.");
            }
        });

    }

    /* ------------------ Soumission du formulaire Admin (Création/Modification) ------------------ */

    if (taskForm) {
        taskForm.addEventListener('submit', function (e) {
            e.preventDefault();
            let url, method;
            if (taskIdInput.value) { // Mode modification
                overrideMethodInput.value = "POST"; // On définit _method=PUT
                url = `${window.Laravel.baseUrl}/tasks/${taskIdInput.value}`;
                method = "POST"; // Envoi en POST avec override _method indiquant PUT
            } else { // Mode création
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
                        // Mode modification : Mise à jour de la carte existante
                        const taskCard = document.querySelector(`.card[data-id="${data.id}"]`);
                        if (taskCard) {
                            const cardTitleElement = taskCard.querySelector('.card-title');
                            if (cardTitleElement) {
                                cardTitleElement.textContent = data.title;
                            }
                            const cardBodyP = taskCard.querySelector('.card-body p');
                            if (cardBodyP) {
                                cardBodyP.textContent = data.description;
                            }
                            taskCard.setAttribute('data-title', data.title);
                            taskCard.setAttribute('data-description', data.description);
                        }

                    } else {
                        // Mode création : Construction d'une nouvelle carte
                        const taskCard = document.createElement('div');
                        taskCard.className = 'card bg-white shadow-md rounded-lg p-4 transition hover:shadow-xl';
                        taskCard.setAttribute('data-id', data.id);
                        taskCard.setAttribute('data-title', data.title);
                        taskCard.setAttribute('data-description', data.description);

                        const cardHeader = document.createElement('div');
                        cardHeader.className = 'flex justify-between items-center';

                        const cardTitle = document.createElement('h3');
                        cardTitle.className = 'card-title text-xl font-bold';
                        cardTitle.textContent = data.title;
                        cardHeader.appendChild(cardTitle);

                        if (window.Laravel.isAdmin === true) {
                            const actionDiv = document.createElement('div');
                            actionDiv.className = 'flex space-x-2';

                            // Bouton suppression
                            const deleteBtn = document.createElement('button');
                            deleteBtn.className = 'delete-btn text-red-500 hover:text-red-700';
                            deleteBtn.textContent = '×';
                            actionDiv.appendChild(deleteBtn);

                            // Bouton édition — version mise à jour
                            const editBtn = document.createElement('button');
                            editBtn.className = ' edit-btn px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none';
                            editBtn.textContent = 'Modifier';
                            editBtn.setAttribute('data-modal-toggle', '#create-task-modal');
                            actionDiv.appendChild(editBtn);
                            cardHeader.appendChild(actionDiv);
                        } else {
                            // Exemple dans la branche de création
                            if (!window.Laravel.isAdmin) {
                                const completeBtn = document.createElement('button');
                                completeBtn.className = 'px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none complete-task-btn';
                                completeBtn.textContent = 'Tâche terminée';
                                cardHeader.appendChild(completeBtn);
                            }

                        }

                        taskCard.appendChild(cardHeader);

                        const cardBody = document.createElement('div');
                        cardBody.className = 'card-body mt-3';

                        const descP = document.createElement('p');
                        descP.className = 'text-gray-700';
                        descP.textContent = data.description;
                        cardBody.appendChild(descP);

                        taskCard.appendChild(cardBody);

                        tasksContainer.appendChild(taskCard);
                        updateNoTaskMessage(); // Supprime le message "Aucune tâche à afficher" s'il est présent
                    }

                    })
                .catch(error => console.error("Erreur:", error));
        });
    }

    /* ------------------ Événement pour ouvrir la modale en mode édition (admin) ------------------ */
    if (tasksContainer) {
        tasksContainer.addEventListener('click', function (e) {
            // Vérifier si on clique sur le bouton "Modifier"
            if (e.target && e.target.classList.contains('edit-btn')) {
                console.log("DEBUG: Edit button clicked.");
                console.log("DEBUG: e.target:", e.target);

                const taskCard = e.target.closest('.card');
                if (!taskCard) {
                    console.warn("DEBUG: Aucune carte ('.card') trouvée pour l'édition. Vérifiez votre structure HTML.");
                    return;
                }
                console.log("DEBUG: taskCard trouvé:", taskCard);

                const id = taskCard.getAttribute('data-id');
                const title = taskCard.getAttribute('data-title');
                const description = taskCard.getAttribute('data-description');
                console.log("DEBUG: Found card attributes:", { id, title, description });

                // Vérifier l'existence des champs du formulaire
                if (!taskIdInput || !titleInput || !descriptionInput || !modalTitle) {
                    console.error("DEBUG: Un ou plusieurs éléments du formulaire d'édition sont introuvables.");
                    return;
                }

                taskIdInput.value = id;
                titleInput.value = title;
                descriptionInput.value = description;

                modalTitle.textContent = "Modifier la tâche";
                console.log("DEBUG: Modal title mis à jour, ouverture du modal.");



            }
        });
    }


    /* ------------------ Suppression d'une tâche (admin) ------------------ */
    if (tasksContainer) {
        tasksContainer.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('delete-btn')) {
                if (confirm("Voulez-vous vraiment supprimer cette tâche ?")) {
                    const taskCard = e.target.closest('.card');
                    if (!taskCard) return;
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
                            updateNoTaskMessage();
                        })
                        .catch(error => console.error("Erreur :", error));
                }
            }
        });
    }



    /* ------------------ Gestion de la modale de soumission (étudiant) ------------------ */
    if (completeTaskButtons && completeTaskButtons.length > 0) {
        completeTaskButtons.forEach(button => {
            button.addEventListener('click', function () {
                const taskCard = button.closest('.card');
                if (!taskCard) return;
                const taskId = taskCard.getAttribute('data-id');
                submissionTaskIdInput.value = taskId;
                if (submissionModal) {
                    submissionModal.classList.remove('hidden');
                    console.log("DEBUG: Ouverture du modal de soumission pour la tâche ID", taskId);
                    console.log(submissionModal.classList);
                }
            });
        });
    }
    if (closeSubmissionModal) {
        closeSubmissionModal.addEventListener('click', () => {
            if (submissionModal) {
                submissionModal.classList.add('hidden');
                console.log("DEBUG: Fermeture du modal de soumission.");
            }
        });
        if (submissionModal) {
            submissionModal.addEventListener('click', function (e) {
                if (e.target === submissionModal) submissionModal.classList.add('hidden');
            });
        }
    }
    if (submissionForm) {
        submissionForm.addEventListener('submit', function (e) {
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
                    if (submissionModal) {
                        submissionModal.classList.add('hidden');
                    }
                    submissionForm.reset();
                })
                .catch(error => console.error("DEBUG: Erreur lors de la soumission du formulaire étudiant:", error));
        });
    }
    // Appel initial pour mettre à jour le message "Aucune tâche à afficher"
    updateNoTaskMessage();
});
