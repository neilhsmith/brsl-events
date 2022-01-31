export interface ILja {
  id: number;
  role: "brsl_freshman" | "brsl_sophomore" | "brsl_junior" | "brsl_senior";
  firstName: string;
  lastName: string;
  notes: string;
  acknowledgesResponsibility: boolean;
  acknowledgesRelinquish: boolean;
  didRelinquish: boolean;
}

export interface ILjasState {
  status: "idle" | "pending" | "fulfilled";
  entities: ILja[] | null;
}
