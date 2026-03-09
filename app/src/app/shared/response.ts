export interface ResponseHttp<T> {
  success: boolean;
  message: string;
  data: T;
}
