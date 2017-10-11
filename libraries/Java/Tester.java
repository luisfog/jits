package tester.jits.github.com;

import java.util.ArrayList;
import java.util.List;

public class Tester {

	public static void main(String[] args) {
		
		Jits jits = new Jits("http://yourserver/",
				"your connection key",
				"your aes key");		
		
		while(true){
			try {
				Thread.sleep(5000);
				
				List<String> names = new ArrayList<String>();
				List<Float> values = new ArrayList<Float>();
				
				names.add("T�mp");
				values.add((float)(Math.random() * 100 + 1));
				names.add("H� m");
				values.add((float)(Math.random() * 100 + 1));
				
				if(jits.sendDataList(names, values))
					System.out.println("Data sent");
				else
					System.out.println("Something went wrong");
								
			} catch (InterruptedException e) {
				e.printStackTrace();
			}
		}
	}

}
