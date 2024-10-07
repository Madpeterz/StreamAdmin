export default interface Interface_Datatable{
    id: number,
    hideColZero: boolean,
    col: number,
    cols: string,
    name: string,
    dir: string
}
export const Default_Datatable: Interface_Datatable = {
    id: 0,
    hideColZero: true,
    col: 0,
    cols: "",
    name: "",
    dir: ""
}