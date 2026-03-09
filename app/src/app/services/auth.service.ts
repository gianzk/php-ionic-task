import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { ResponseHttp } from '../shared/response';
import { LoginResponse, UserDto, UserResponse } from './interfaces/user.interface';
import { environment } from 'src/environments/environment';
import { resolveApiUrl } from '../shared/api-url';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly apiUrl = resolveApiUrl(environment.api_url);

  constructor(private http: HttpClient) {
  }

  async registerUser(name: string, email: string, password: string): Promise<UserResponse> {
    const body: UserDto = { name, email, password };
    const response = await firstValueFrom(
      this.http.post<ResponseHttp<UserResponse>>(`${this.apiUrl}/register`, body)
    );

    return response.data;
  }

  async login(email: string, password: string): Promise<LoginResponse> {
    const body = { email, password };
    const response = await firstValueFrom(
      this.http.post<ResponseHttp<LoginResponse>>(`${this.apiUrl}/login`, body)
    );

    return response.data;
  }

  saveSession(data: LoginResponse): void {
    localStorage.setItem('token', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));
  }

  logout(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  }

  isLoggedIn(): boolean {
    return !!localStorage.getItem('token');
  }

  getToken(): string | null {
    return localStorage.getItem('token');
  }

  getCurrentUser(): UserResponse | null {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }
}
