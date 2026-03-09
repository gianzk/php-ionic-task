import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Task, TaskResponse, ApiResponse } from './interfaces/task.interface';
import { AuthService } from './auth.service';
import { environment } from 'src/environments/environment';
import { resolveApiUrl } from '../shared/api-url';

@Injectable({
  providedIn: 'root'
})
export class TaskService {
  private apiUrl = resolveApiUrl(environment.api_url);

  constructor(
    private http: HttpClient,
    private authService: AuthService
  ) {}

  private getHeaders(): HttpHeaders {
    const token = this.authService.getToken();
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': '*/*'
    };

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    return new HttpHeaders(headers);
  }

  getTasks(): Observable<TaskResponse> {
    return this.http.get<TaskResponse>(`${this.apiUrl}/tasks`, {
      headers: this.getHeaders()
    });
  }

  createTask(task: Partial<Task>): Observable<ApiResponse<Task>> {
    return this.http.post<ApiResponse<Task>>(`${this.apiUrl}/tasks`, task, {
      headers: this.getHeaders()
    });
  }

  updateTask(id: number, task: Partial<Task>): Observable<ApiResponse<Task>> {
    return this.http.put<ApiResponse<Task>>(`${this.apiUrl}/tasks/${id}`, task, {
      headers: this.getHeaders()
    });
  }

  deleteTask(id: number): Observable<ApiResponse<any>> {
    return this.http.delete<ApiResponse<any>>(`${this.apiUrl}/tasks/${id}`, {
      headers: this.getHeaders()
    });
  }

  toggleTaskComplete(id: number, completed: boolean): Observable<ApiResponse<Task>> {
    return this.http.put<ApiResponse<Task>>(`${this.apiUrl}/tasks/${id}`,
      { completed },
      { headers: this.getHeaders() }
    );
  }
}
