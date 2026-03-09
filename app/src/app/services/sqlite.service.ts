import { Injectable } from '@angular/core';
import { Task } from './interfaces/task.interface';

export interface OfflineTask {
  localId: number;
  title: string;
  description: string;
  completed: boolean;
  created_at: string;
}

@Injectable({
  providedIn: 'root'
})
export class SqliteService {
  private readonly STORAGE_KEY = 'offline_tasks';

  getOfflineTasks(): OfflineTask[] {
    const data = localStorage.getItem(this.STORAGE_KEY);
    return data ? JSON.parse(data) : [];
  }

  addOfflineTask(task: { title: string; description: string; completed: boolean }): OfflineTask {
    const tasks = this.getOfflineTasks();
    const newTask: OfflineTask = {
      localId: Date.now(),
      title: task.title,
      description: task.description,
      completed: task.completed,
      created_at: new Date().toISOString()
    };
    tasks.push(newTask);
    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(tasks));
    return newTask;
  }

  updateOfflineTask(localId: number, data: Partial<OfflineTask>): OfflineTask | null {
    const tasks = this.getOfflineTasks();
    const index = tasks.findIndex(t => t.localId === localId);
    if (index === -1) return null;
    tasks[index] = { ...tasks[index], ...data };
    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(tasks));
    return tasks[index];
  }

  deleteOfflineTask(localId: number): boolean {
    const tasks = this.getOfflineTasks();
    const filtered = tasks.filter(t => t.localId !== localId);
    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(filtered));
    return filtered.length < tasks.length;
  }

  clearOfflineTasks(): void {
    localStorage.removeItem(this.STORAGE_KEY);
  }

  hasOfflineTasks(): boolean {
    return this.getOfflineTasks().length > 0;
  }
}
