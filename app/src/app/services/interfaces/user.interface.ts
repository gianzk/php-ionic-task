export interface UserDto {
  name: string;
  email: string;
  password: string;
}

export interface UserResponse {
  id: number;
  name: string;
  email: string;
  created_at?: string;
  updated_at?: string;
}

export interface LoginResponse {
  token: string;
  token_type: string;
  expires_in: number;
  user : UserResponse;
}
