import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { Router } from '@angular/router';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import {
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonFab,
  IonFabButton,
  IonIcon,
  IonGrid,
  IonRow,
  IonCol,
  IonCard,
  IonCardContent,
  IonCardHeader,
  IonCardTitle,
  IonCheckbox,
  IonButton,
  IonModal,
  IonInput,
  IonTextarea,
  IonItem,
  IonMenuButton,
  IonButtons,
  IonSpinner,
  IonText
} from '@ionic/angular/standalone';
import { ActionSheetController } from '@ionic/angular';
import { Network } from '@capacitor/network';
import { addIcons } from 'ionicons';
import { add, checkmark, menu, refresh, createOutline, trashOutline, syncOutline } from 'ionicons/icons';
import { TaskService } from '../../services/task.service';
import { Task } from '../../services/interfaces/task.interface';
import { AuthService } from '../../services/auth.service';
import { SqliteService, OfflineTask } from '../../services/sqlite.service';

@Component({
  selector: 'app-task',
  templateUrl: './task.page.html',
  styleUrls: ['./task.page.scss'],
  imports: [
    CommonModule,
    HttpClientModule,
    ReactiveFormsModule,
    IonHeader,
    IonToolbar,
    IonTitle,
    IonContent,
    IonFab,
    IonFabButton,
    IonIcon,
    IonGrid,
    IonRow,
    IonCol,
    IonCard,
    IonCardContent,
    IonCardHeader,
    IonCardTitle,
    IonCheckbox,
    IonButton,
    IonModal,
    IonInput,
    IonTextarea,
    IonItem,
    IonMenuButton,
    IonButtons,
    IonSpinner,
    IonText
  ],
})
export class TaskPage implements OnInit {
  isConnected = true;
  isOfflineMode = false;
  tasks: Task[] = [];
  offlineTasks: OfflineTask[] = [];
  loading = true;
  error = '';
  isCreateModalOpen = false;
  creatingTask = false;
  createTaskError = '';
  isEditMode = false;
  editingTaskId: number | null = null;
  editingLocalId: number | null = null;
  updatingTaskIds = new Set<number>();
  syncing = false;
  syncProgress = '';

  createTaskForm = new FormGroup({
    title: new FormControl('', [Validators.required, Validators.minLength(2)]),
    description: new FormControl('', [Validators.required, Validators.minLength(2)])
  });

  constructor(
    private taskService: TaskService,
    private authService: AuthService,
    private router: Router,
    private actionSheetCtrl: ActionSheetController,
    private sqliteService: SqliteService
  ) {
    addIcons({ add, checkmark, menu, refresh, createOutline, trashOutline, syncOutline });
  }

  ngOnInit() {
    this.loadTasks();

    Network.addListener('networkStatusChange', status => {
      console.log('Network status changed', status);
      this.isConnected = status.connected;
    });
  }

  async loadTasks() {
    try {
      this.loading = true;
      this.error = '';

      this.taskService.getTasks().subscribe({
        next: (response) => {
          if (response.success) {
            this.tasks = response.data;
            this.isOfflineMode = false;
          } else {
            this.error = 'Error loading tasks';
          }
          this.loading = false;
        },
        error: (err) => {
          console.error('Error loading tasks:', err);
          if (err?.status === 401) {
            this.error = 'Session expired. Please login again.';
            this.authService.logout();
            this.router.navigate(['/login']);
            this.loading = false;
            return;
          }
          this.isOfflineMode = true;
          this.tasks = [];
          this.offlineTasks = this.sqliteService.getOfflineTasks();
          this.loading = false;
        }
      });
    } catch (error) {
      console.error('Error loading tasks:', error);
      this.isOfflineMode = true;
      this.tasks = [];
      this.offlineTasks = this.sqliteService.getOfflineTasks();
      this.loading = false;
    }
  }

  addTask() {
    this.openCreateTaskModal();
  }

  openCreateTaskModal() {
    this.isEditMode = false;
    this.editingTaskId = null;
    this.editingLocalId = null;
    this.createTaskError = '';
    this.createTaskForm.reset({ title: '', description: '' });
    this.isCreateModalOpen = true;
  }

  openEditTaskModal(task: Task) {
    this.isEditMode = true;
    this.editingTaskId = task.id;
    this.editingLocalId = null;
    this.createTaskError = '';
    this.createTaskForm.reset({
      title: task.title,
      description: task.description
    });
    this.isCreateModalOpen = true;
  }

  openEditOfflineTaskModal(task: OfflineTask) {
    this.isEditMode = true;
    this.editingTaskId = null;
    this.editingLocalId = task.localId;
    this.createTaskError = '';
    this.createTaskForm.reset({
      title: task.title,
      description: task.description
    });
    this.isCreateModalOpen = true;
  }

  closeCreateTaskModal() {
    this.isCreateModalOpen = false;
  }

  submitCreateTask() {
    if (this.createTaskForm.invalid || this.creatingTask) {
      this.createTaskForm.markAllAsTouched();
      return;
    }

    const title = this.createTaskForm.value.title?.trim() || '';
    const description = this.createTaskForm.value.description?.trim() || '';

    if (!title || !description) {
      this.createTaskError = 'Titulo y descripcion son obligatorios.';
      return;
    }

    if (this.isOfflineMode) {
      if (this.isEditMode && this.editingLocalId) {
        this.sqliteService.updateOfflineTask(this.editingLocalId, { title, description });
      } else {
        this.sqliteService.addOfflineTask({ title, description, completed: false });
      }
      this.offlineTasks = this.sqliteService.getOfflineTasks();
      this.closeCreateTaskModal();
      return;
    }

    // Online mode: send to API
    this.creatingTask = true;
    this.createTaskError = '';

    const request$ = this.isEditMode && this.editingTaskId
      ? this.taskService.updateTask(this.editingTaskId, { title, description })
      : this.taskService.createTask({ title, description, completed: false });

    request$.subscribe({
      next: (response) => {
        this.creatingTask = false;
        if (response.success) {
          this.closeCreateTaskModal();
          this.loadTasks();
          return;
        }
        this.createTaskError = response.message || 'No se pudo guardar la tarea.';
      },
      error: (err) => {
        this.creatingTask = false;
        if (err?.status === 401) {
          this.authService.logout();
          this.router.navigate(['/login']);
          return;
        }
        this.createTaskError = err?.error?.message || 'Error al guardar la tarea.';
      }
    });
  }

  async requestDeleteTask(task: Task) {
    const actionSheet = await this.actionSheetCtrl.create({
      header: 'Confirmar eliminacion',
      subHeader: 'Esta accion no se puede deshacer',
      buttons: [
        {
          text: 'Eliminar',
          role: 'destructive',
          handler: () => this.confirmDeleteTask(task)
        },
        {
          text: 'Cancelar',
          role: 'cancel'
        }
      ]
    });

    await actionSheet.present();
  }

  confirmDeleteTask(task: Task) {

    this.taskService.deleteTask(task.id).subscribe({
      next: (response) => {
        if (response.success) {
          this.tasks = this.tasks.filter((t) => t.id !== task.id);
        }
      },
      error: (err) => {
        if (err?.status === 401) {
          this.authService.logout();
          this.router.navigate(['/login']);
          return;
        }
        console.error('Error deleting task:', err);
      }
    });
  }

  async requestDeleteOfflineTask(task: OfflineTask) {
    const actionSheet = await this.actionSheetCtrl.create({
      header: 'Confirmar eliminacion',
      subHeader: 'Esta accion no se puede deshacer',
      buttons: [
        {
          text: 'Eliminar',
          role: 'destructive',
          handler: () => {
            this.sqliteService.deleteOfflineTask(task.localId);
            this.offlineTasks = this.sqliteService.getOfflineTasks();
          }
        },
        {
          text: 'Cancelar',
          role: 'cancel'
        }
      ]
    });

    await actionSheet.present();
  }

  async requestToggleOfflineTask(task: OfflineTask) {
    const nextCompleted = !task.completed;
    const actionSheet = await this.actionSheetCtrl.create({
      header: 'Confirmar estado',
      subHeader: nextCompleted
        ? 'Marcar tarea como completada?'
        : 'Marcar tarea como pendiente?',
      buttons: [
        {
          text: 'Confirmar',
          handler: () => {
            this.sqliteService.updateOfflineTask(task.localId, { completed: nextCompleted });
            this.offlineTasks = this.sqliteService.getOfflineTasks();
          }
        },
        {
          text: 'Cancelar',
          role: 'cancel'
        }
      ]
    });

    await actionSheet.present();
  }

  isTaskUpdating(taskId: number): boolean {
    return this.updatingTaskIds.has(taskId);
  }

  async requestToggleTask(task: Task) {
    if (this.isTaskUpdating(task.id)) {
      return;
    }

    const nextCompleted = !task.completed;
    const actionSheet = await this.actionSheetCtrl.create({
      header: 'Confirmar estado',
      subHeader: nextCompleted
        ? 'Marcar tarea como completada?'
        : 'Marcar tarea como pendiente?',
      buttons: [
        {
          text: 'Confirmar',
          handler: () => this.confirmToggleTask(task, nextCompleted)
        },
        {
          text: 'Cancelar',
          role: 'cancel'
        }
      ]
    });

    await actionSheet.present();
  }

  confirmToggleTask(task: Task, completed: boolean) {
    this.updatingTaskIds.add(task.id);

    this.taskService.updateTask(task.id, {
      title: task.title,
      description: task.description,
      completed
    }).subscribe({
      next: (response) => {
        this.updatingTaskIds.delete(task.id);
        if (response.success) {
          task.completed = completed;
        } else {
          console.error('Failed to update task status');
        }
      },
      error: (err) => {
        this.updatingTaskIds.delete(task.id);
        console.error('Error updating task status:', err);
        if (err?.status === 401) {
          this.authService.logout();
          this.router.navigate(['/login']);
        }
      }
    });
  }

  async syncOfflineTasks() {
    if (this.syncing || this.offlineTasks.length === 0) return;

    this.syncing = true;
    const tasksToSync = [...this.offlineTasks];
    let synced = 0;
    let failed = 0;

    for (const offlineTask of tasksToSync) {
      this.syncProgress = `Sincronizando ${synced + 1} de ${tasksToSync.length}...`;

      try {
        await new Promise<void>((resolve, reject) => {
          this.taskService.createTask({
            title: offlineTask.title,
            description: offlineTask.description,
            completed: offlineTask.completed
          }).subscribe({
            next: (response) => {
              if (response.success) {
                this.sqliteService.deleteOfflineTask(offlineTask.localId);
                synced++;
              } else {
                failed++;
              }
              resolve();
            },
            error: (err) => {
              if (err?.status === 401) {
                this.authService.logout();
                this.router.navigate(['/login']);
                reject(err);
                return;
              }
              failed++;
              resolve();
            }
          });
        });
      } catch {
        break;
      }
    }

    this.syncing = false;
    this.syncProgress = '';
    this.offlineTasks = this.sqliteService.getOfflineTasks();

    if (synced > 0) {
      this.loadTasks();
    }

    if (failed > 0) {
      this.error = `Se sincronizaron ${synced} tareas. ${failed} fallaron.`;
    }
  }

  async refreshTasks(event?: any) {
    await this.loadTasks();
    if (event) {
      event.target.complete();
    }
  }
}
