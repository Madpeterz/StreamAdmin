export default interface Interface_Eventsq{
    id: number,
    eventName: string,
    eventMessage: string,
    eventUnixtime: number
}
export const Default_Eventsq: Interface_Eventsq = {
    id: 0,
    eventName: "",
    eventMessage: "",
    eventUnixtime: 0
}