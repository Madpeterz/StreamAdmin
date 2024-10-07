export default interface Interface_Rental{
    id: number,
    avatarLink: number,
    streamLink: number,
    packageLink: number,
    noticeLink: number,
    startUnixtime: number,
    expireUnixtime: number,
    renewals: number,
    totalAmount: number,
    message: string,
    rentalUid: string
}
export const Default_Rental: Interface_Rental = {
    id: 0,
    avatarLink: 0,
    streamLink: 0,
    packageLink: 0,
    noticeLink: 0,
    startUnixtime: 0,
    expireUnixtime: 0,
    renewals: 0,
    totalAmount: 0,
    message: "",
    rentalUid: ""
}