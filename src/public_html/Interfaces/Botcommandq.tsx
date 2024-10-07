export default interface Interface_Botcommandq{
    id: number,
    command: string,
    args: string,
    unixtime: number
}
export const Default_Botcommandq: Interface_Botcommandq = {
    id: 0,
    command: "",
    args: "",
    unixtime: 0
}